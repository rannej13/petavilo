function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

export function installVideoFacades(root: ParentNode = document): void {
    root.querySelectorAll<HTMLButtonElement>('[data-video-embed]').forEach((button) => {
        if (button.dataset['videoReady'] === '1') {
            return;
        }

        button.dataset['videoReady'] = '1';
        button.addEventListener('click', () => {
            const src = button.dataset['videoEmbed'] ?? '';
            const frame = button.closest<HTMLElement>('.tv-frame');

            if (src === '' || frame === null || !src.startsWith('https://')) {
                return;
            }

            const iframe = document.createElement('iframe');
            iframe.src = src;
            iframe.title = button.dataset['videoTitle'] ?? 'Video';
            iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
            iframe.allowFullscreen = true;
            iframe.referrerPolicy = 'strict-origin-when-cross-origin';
            frame.replaceChildren(iframe);

            const played = button.dataset['videoPlayed'] ?? '';

            if (played !== '') {
                void fetch(played, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
                    credentials: 'same-origin',
                }).catch(() => undefined);
            }
        });
    });
}
