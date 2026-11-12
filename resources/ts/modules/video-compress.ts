interface UploadProgressEvent {
    detail: { progress: number };
}

interface LivewireComponent {
    uploadMultiple(
        name: string,
        files: File[],
        finish?: () => void,
        error?: () => void,
        progress?: (event: UploadProgressEvent) => void,
    ): void;
}

interface LivewireGlobal {
    find(id: string): LivewireComponent | undefined;
}

type CapturableCanvas = HTMLCanvasElement & { captureStream(frameRate?: number): MediaStream };

const COMPRESS_ABOVE_BYTES = 25 * 1024 * 1024;
const TARGET_WIDTH = 854;
const MIME_CANDIDATES = [
    'video/mp4;codecs=avc1.42E01E,mp4a.40.2',
    'video/mp4',
    'video/webm;codecs=vp9,opus',
    'video/webm;codecs=vp8,opus',
    'video/webm',
];

let installed = false;

function supportedMime(): string | null {
    if (typeof MediaRecorder === 'undefined' || typeof MediaRecorder.isTypeSupported !== 'function') {
        return null;
    }

    return MIME_CANDIDATES.find((mime) => MediaRecorder.isTypeSupported(mime)) ?? null;
}

function canCaptureCanvas(): boolean {
    return typeof (HTMLCanvasElement.prototype as Partial<CapturableCanvas>).captureStream === 'function';
}

async function loadVideo(file: File): Promise<{ video: HTMLVideoElement; url: string }> {
    const url = URL.createObjectURL(file);
    const video = document.createElement('video');

    video.src = url;
    video.playsInline = true;
    video.preload = 'auto';

    await new Promise<void>((resolve, reject) => {
        video.onloadedmetadata = (): void => resolve();
        video.onerror = (): void => reject(new Error('This video could not be read.'));
    });

    return { video, url };
}

async function compressVideo(file: File, onProgress: (fraction: number) => void): Promise<File> {
    const mime = supportedMime();

    if (mime === null || !canCaptureCanvas()) {
        return file;
    }

    const { video, url } = await loadVideo(file);
    const scale = Math.min(1, TARGET_WIDTH / (video.videoWidth || TARGET_WIDTH));
    const width = Math.max(2, Math.round((video.videoWidth * scale) / 2) * 2);
    const height = Math.max(2, Math.round((video.videoHeight * scale) / 2) * 2);
    const canvas = document.createElement('canvas') as CapturableCanvas;

    canvas.width = width;
    canvas.height = height;

    const context = canvas.getContext('2d');

    if (context === null) {
        URL.revokeObjectURL(url);

        return file;
    }

    const stream = canvas.captureStream(30);
    let audioContext: AudioContext | null = null;

    try {
        audioContext = new AudioContext();
        const source = audioContext.createMediaElementSource(video);
        const destination = audioContext.createMediaStreamDestination();

        source.connect(destination);
        destination.stream.getAudioTracks().forEach((track) => stream.addTrack(track));
    } catch {
        video.muted = true;
    }

    const recorder = new MediaRecorder(stream, {
        mimeType: mime,
        videoBitsPerSecond: 1_500_000,
        audioBitsPerSecond: 96_000,
    });
    const chunks: Blob[] = [];

    recorder.ondataavailable = (event: BlobEvent): void => {
        if (event.data.size > 0) {
            chunks.push(event.data);
        }
    };

    const stopped = new Promise<void>((resolve) => {
        recorder.onstop = (): void => resolve();
    });
    const ended = new Promise<void>((resolve) => {
        video.onended = (): void => resolve();
    });

    let frame = 0;
    const draw = (): void => {
        context.drawImage(video, 0, 0, width, height);

        if (Number.isFinite(video.duration) && video.duration > 0) {
            onProgress(Math.min(1, video.currentTime / video.duration));
        }

        if (!video.ended) {
            frame = requestAnimationFrame(draw);
        }
    };

    recorder.start(1000);

    try {
        await video.play();
    } catch {
        video.muted = true;
        await video.play();
    }

    draw();
    await ended;
    cancelAnimationFrame(frame);
    recorder.stop();
    await stopped;

    if (audioContext !== null) {
        void audioContext.close();
    }

    URL.revokeObjectURL(url);

    const type = mime.split(';')[0] ?? 'video/webm';
    const extension = type === 'video/mp4' ? 'mp4' : 'webm';
    const result = new File(chunks, `${file.name.replace(/\.[^.]+$/, '')}.${extension}`, { type });

    return result.size > 0 && result.size < file.size ? result : file;
}

function componentFor(input: HTMLElement): LivewireComponent | undefined {
    const root = input.closest<HTMLElement>('[wire\\:id]');
    const livewire = (window as unknown as { Livewire?: LivewireGlobal }).Livewire;
    const id = root?.getAttribute('wire:id') ?? '';

    return id !== '' && livewire !== undefined ? livewire.find(id) : undefined;
}

async function handleChange(input: HTMLInputElement): Promise<void> {
    const files = Array.from(input.files ?? []);
    const component = componentFor(input);
    const status = document.getElementById(input.dataset['videoStatus'] ?? '');
    const setStatus = (text: string): void => {
        if (status !== null) {
            status.textContent = text;
        }
    };

    if (files.length === 0 || component === undefined) {
        return;
    }

    const prepared: File[] = [];

    for (const [index, file] of files.entries()) {
        if (file.size <= COMPRESS_ABOVE_BYTES) {
            prepared.push(file);
            continue;
        }

        try {
            const label = files.length > 1 ? `video ${index + 1} of ${files.length}` : 'your video';
            setStatus(`Making ${label} smaller... keep this screen open.`);
            prepared.push(await compressVideo(file, (fraction) => {
                setStatus(`Making ${label} smaller: ${Math.round(fraction * 100)}%. Keep this screen open.`);
            }));
        } catch {
            prepared.push(file);
        }
    }

    setStatus('Uploading...');
    component.uploadMultiple(
        input.dataset['videoCompress'] ?? 'videos',
        prepared,
        () => setStatus(''),
        () => setStatus('The upload failed. Try a shorter clip (under a minute).'),
        (event) => setStatus(`Uploading ${Math.round(event.detail.progress)}%`),
    );
    input.value = '';
}

export function installVideoCompressor(): void {
    if (installed) {
        return;
    }

    installed = true;
    document.addEventListener('change', (event: Event) => {
        const target = event.target;

        if (target instanceof HTMLInputElement && target.matches('input[data-video-compress]')) {
            void handleChange(target);
        }
    });
}
