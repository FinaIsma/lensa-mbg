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
    @vite(['resources/css/staff/lots-print.css', 'resources/js/staff/lots-print.js'])
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
                    <div id="qrcode-canvas" data-barcode="{{ $lot->barcode }}"></div>
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

</body>
</html>
