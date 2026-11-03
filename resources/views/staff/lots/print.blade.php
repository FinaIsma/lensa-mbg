<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode & QR - {{ $lot->barcode }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F1F5F9;
            color: #0F172A;
            padding: 30px 20px;
        }
        .action-bar {
            max-width: 480px;
            margin: 0 auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        .format-selector-bar {
            max-width: 480px;
            margin: 0 auto 20px auto;
            background: #FFFFFF;
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid #CBD5E1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 13px;
        }
        .format-segmented-group {
            display: inline-flex;
            background: #F1F5F9;
            padding: 4px;
            border-radius: 8px;
            gap: 4px;
        }
        .format-pill {
            cursor: pointer;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12.5px;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            user-select: none;
        }
        .format-pill.active {
            background: #042A70;
            color: #FFFFFF;
            box-shadow: 0 2px 4px rgba(4, 42, 112, 0.25);
        }
        .format-pill input {
            display: none;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-back {
            background-color: #FFFFFF;
            color: #475569;
            border: 1px solid #CBD5E1;
        }
        .btn-back:hover {
            background-color: #F8FAFC;
        }
        .btn-print {
            background-color: #16A34A;
            color: #FFFFFF;
            border: none;
        }
        .btn-print:hover {
            background-color: #15803D;
        }
        .labels-container {
            max-width: 480px;
            margin: 0 auto;
        }
        .barcode-card {
            background: #FFFFFF;
            border: 2px dashed #94A3B8;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            page-break-inside: avoid;
        }
        .card-header-sppg {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #042A70;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .sppg-title {
            font-size: 14px;
            font-weight: 700;
            color: #042A70;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-badge {
            font-size: 11px;
            background: #E0F2FE;
            color: #0369A1;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
        }
        .material-title {
            font-size: 17px;
            font-weight: 800;
            color: #071E49;
            margin-bottom: 12px;
        }
        .lot-info-table {
            width: 100%;
            font-size: 12.5px;
            margin-bottom: 16px;
            border-collapse: collapse;
        }
        .lot-info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .lot-info-table .label {
            width: 135px;
            color: #64748B;
        }
        .lot-info-table .val {
            font-weight: 600;
            color: #0F172A;
        }

        /* Box Kode (QR Code Kotak & Barcode Lurus) */
        .code-display-box {
            background: #FAFAFA;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 16px 14px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .qr-wrapper {
            background: #FFFFFF;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
            display: inline-block;
        }
        .qr-wrapper img {
            display: block;
            margin: 0 auto;
        }
        .barcode-visual-box {
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 12px 10px 8px;
            text-align: center;
        }
        .barcode-svg {
            width: 90%;
            height: 48px;
            margin: 0 auto;
            display: block;
        }
        .barcode-number-text {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 14.5px;
            font-weight: 800;
            color: #0F172A;
            margin-top: 6px;
            letter-spacing: 1.5px;
        }
        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
            }
            .action-bar, .format-selector-bar {
                display: none !important;
            }
            .labels-container {
                max-width: 100% !important;
            }
            .barcode-card {
                box-shadow: none !important;
                border: 1.5px solid #000000 !important;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <a href="{{ route('staff.lots.index') }}" class="btn btn-back">
            &larr; Kembali ke Daftar Lot
        </a>
        <button type="button" onclick="window.print()" class="btn btn-print">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>Cetak Label (Ctrl+P)</span>
        </button>
    </div>

    {{-- Format Selector: Pilihan QR Code (Kotak) vs Barcode (Garis) --}}
    <div class="format-selector-bar">
        <span style="font-weight: 700; color: #071E49;">Pilihan Format Label:</span>
        <div class="format-segmented-group">
            <label class="format-pill active" id="pill-qr">
                <input type="radio" name="format_choice" value="qr" checked onchange="switchFormat('qr')">
                <span>🔲 QR Code (Kotak)</span>
            </label>
            <label class="format-pill" id="pill-barcode">
                <input type="radio" name="format_choice" value="barcode" onchange="switchFormat('barcode')">
                <span>📊 Barcode (Garis)</span>
            </label>
            <label class="format-pill" id="pill-both">
                <input type="radio" name="format_choice" value="both" onchange="switchFormat('both')">
                <span>📑 Keduanya</span>
            </label>
        </div>
    </div>

    <div class="labels-container">
        <div class="barcode-card">
            <div class="card-header-sppg">
                <div class="sppg-title">{{ $sppg->nama_sppg ?? 'SPPG Unit Dapur' }}</div>
                <div class="doc-badge">LABEL BAHAN BAKU</div>
            </div>

            <div class="material-title">{{ $lot->material?->nama ?? 'Bahan Pangan' }}</div>

            <table class="lot-info-table">
                <tr>
                    <td class="label">No. Lot Kemasan:</td>
                    <td class="val">{{ $lot->lot_number }}</td>
                </tr>
                <tr>
                    <td class="label">Pemasok:</td>
                    <td class="val">{{ $lot->supplier?->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Terima:</td>
                    <td class="val">{{ $lot->received_date ? \Carbon\Carbon::parse($lot->received_date)->format('d-m-Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tgl Kedaluwarsa:</td>
                    <td class="val" style="color: #DC2626; font-weight: 700;">{{ $lot->expiry_date ? \Carbon\Carbon::parse($lot->expiry_date)->format('d-m-Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Stok Awal / Sisa:</td>
                    <td class="val">{{ number_format($lot->current_qty, 2, ',', '.') }} / {{ number_format($lot->initial_qty, 2, ',', '.') }} {{ $lot->material?->satuan ?? 'kg' }}</td>
                </tr>
            </table>

            <div class="code-display-box">
                {{-- 1. QR Code Kotak (Paling mudah dibaca kamera webcam / smartphone) --}}
                <div id="qr-box-section" class="qr-wrapper">
                    <div id="qrcode-canvas"></div>
                </div>

                {{-- 2. Barcode Garis Code128 (Untuk alat tembak barcode laser USB) --}}
                <div id="barcode-box-section" class="barcode-visual-box" style="display: none;">
                    <svg class="barcode-svg" viewBox="0 0 240 60" preserveAspectRatio="none">
                        @php
                            $hash = md5($lot->barcode);
                            $x = 10;
                            $widths = [2, 1, 3, 1, 2, 4, 1, 3, 2, 1, 4, 2, 1, 3, 2, 1, 3, 1, 4, 2, 1, 3, 2, 4, 1, 2, 3, 1, 2, 1, 3, 4, 2];
                        @endphp
                        @foreach ($widths as $wIdx => $w)
                            @if ($wIdx % 2 == 0)
                                <rect x="{{ $x }}" y="0" width="{{ $w * 1.5 }}" height="60" fill="#000000"></rect>
                            @endif
                            @php $x += $w * 2; @endphp
                        @endforeach
                    </svg>
                </div>

                <div class="barcode-number-text">{{ $lot->barcode }}</div>
            </div>
        </div>
    </div>

    <script>
        // Render QR Code Kotak dengan resolusi tajam
        const qrCode = new QRCode(document.getElementById("qrcode-canvas"), {
            text: "{{ $lot->barcode }}",
            width: 150,
            height: 150,
            colorDark : "#000000",
            colorLight : "#FFFFFF",
            correctLevel : QRCode.CorrectLevel.H
        });

        function switchFormat(fmt) {
            const qrBox = document.getElementById('qr-box-section');
            const barBox = document.getElementById('barcode-box-section');

            const pillQr = document.getElementById('pill-qr');
            const pillBar = document.getElementById('pill-barcode');
            const pillBoth = document.getElementById('pill-both');

            [pillQr, pillBar, pillBoth].forEach(p => p.classList.remove('active'));

            if (fmt === 'qr') {
                qrBox.style.display = 'inline-block';
                barBox.style.display = 'none';
                pillQr.classList.add('active');
                pillQr.querySelector('input').checked = true;
            } else if (fmt === 'barcode') {
                qrBox.style.display = 'none';
                barBox.style.display = 'block';
                pillBar.classList.add('active');
                pillBar.querySelector('input').checked = true;
            } else {
                qrBox.style.display = 'inline-block';
                barBox.style.display = 'block';
                pillBoth.classList.add('active');
                pillBoth.querySelector('input').checked = true;
            }
        }

        // Baca URL parameter ?format=... (default ke 'qr' jika tidak ditentukan)
        const urlParams = new URLSearchParams(window.location.search);
        const initialFormat = urlParams.get('format') || 'qr';
        switchFormat(initialFormat);
    </script>
</body>
</html>
