/**
 * Cetak Label Barcode & QR - Staff SPPG
 */
document.addEventListener('DOMContentLoaded', function () {
    const qrCanvas = document.getElementById('qrcode-canvas');
    if (qrCanvas && typeof QRCode !== 'undefined') {
        const barcodeText = qrCanvas.getAttribute('data-barcode') || '';
        new QRCode(qrCanvas, {
            text: barcodeText,
            width: 150,
            height: 150,
            colorDark: '#000000',
            colorLight: '#FFFFFF',
            correctLevel: QRCode.CorrectLevel.H,
        });
    }

    function switchFormat(fmt) {
        const qrBox = document.getElementById('qr-box-section');
        const barBox = document.getElementById('barcode-box-section');

        const pillQr = document.getElementById('pill-qr');
        const pillBar = document.getElementById('pill-barcode');
        const pillBoth = document.getElementById('pill-both');

        if (!qrBox || !barBox || !pillQr || !pillBar || !pillBoth) return;

        [pillQr, pillBar, pillBoth].forEach((p) => p.classList.remove('active'));

        if (fmt === 'qr') {
            qrBox.style.display = 'inline-block';
            barBox.style.display = 'none';
            pillQr.classList.add('active');
            const input = pillQr.querySelector('input');
            if (input) input.checked = true;
        } else if (fmt === 'barcode') {
            qrBox.style.display = 'none';
            barBox.style.display = 'block';
            pillBar.classList.add('active');
            const input = pillBar.querySelector('input');
            if (input) input.checked = true;
        } else {
            qrBox.style.display = 'inline-block';
            barBox.style.display = 'block';
            pillBoth.classList.add('active');
            const input = pillBoth.querySelector('input');
            if (input) input.checked = true;
        }
    }

    window.switchFormat = switchFormat;

    // Baca URL parameter ?format=... (default ke 'qr' jika tidak ditentukan)
    const urlParams = new URLSearchParams(window.location.search);
    const initialFormat = urlParams.get('format') || 'qr';
    switchFormat(initialFormat);
});

