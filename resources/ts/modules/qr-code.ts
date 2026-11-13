import QRCode from 'qrcode';

export function renderQrCodes(root: ParentNode = document): void {
    root.querySelectorAll<HTMLElement>('[data-qr]').forEach((element) => {
        if (element.dataset['qrReady'] === '1') {
            return;
        }

        const value = element.dataset['qr'] ?? '';

        if (value === '') {
            return;
        }

        element.dataset['qrReady'] = '1';

        QRCode.toDataURL(value, { errorCorrectionLevel: 'M', margin: 2, width: 480 })
            .then((url: string) => {
                const image = document.createElement('img');
                image.src = url;
                image.alt = `QR code for ${element.dataset['qrLabel'] ?? 'this pass'}`;
                image.width = 200;
                image.height = 200;
                image.style.width = '100%';
                image.style.height = 'auto';
                element.replaceChildren(image);
            })
            .catch(() => {
                element.dataset['qrReady'] = '';
            });
    });
}
