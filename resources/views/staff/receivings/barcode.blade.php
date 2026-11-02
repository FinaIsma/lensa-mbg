<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode Lot - Lensa MBG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            max-width: 800px;
            margin: 0 auto 24px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
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
            max-width: 800px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 20px;
        }
        .barcode-card {
            background: #FFFFFF;
            border: 2px dashed #94A3B8;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            page-break-inside: avoid;
        }
        .card-header-sppg {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #042A70;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .sppg-title {
            font-size: 15px;
            font-weight: 700;
            color: #042A70;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-lot {
            background: #E2E8F0;
            color: #0F172A;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
        }
        .material-title {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 8px;
        }
        .lot-info-table {
            width: 100%;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .lot-info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .lot-info-table .label {
            color: #64748B;
            width: 120px;
        }
        .lot-info-table .val {
            font-weight: 600;
            color: #1E293B;
        }
        .barcode-visual-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 16px 12px 12px;
            text-align: center;
        }
        .barcode-svg {
            width: 85%;
            height: 52px;
            margin: 0 auto;
            display: block;
        }
        .barcode-number-text {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 13.5px;
            font-weight: 700;
            color: #0F172A;
            margin-top: 8px;
            letter-spacing: 1.5px;
        }
        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
            }
            .action-bar {
                display: none !important;
            }
            .labels-container {
                max-width: 100% !important;
                gap: 16px !important;
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
        <a href="{{ route('staff.receivings.index') }}" class="btn btn-back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Daftar Penerimaan
        </a>

        <button onclick="window.print()" class="btn btn-print">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Cetak Label
        </button>
    </div>

    <div class="labels-container">
        @forelse ($lots as $lot)
            @php
                $material = $lot->material;
                $supplier = $lot->supplier ?? $receiving->purchase?->supplier;
            @endphp
            <div class="barcode-card">
                <div class="card-header-sppg">
                    <span class="sppg-title">{{ $sppg->nama_sppg ?? 'SPPG MBG' }}</span>
                    <span class="badge-lot">LOT BAHAN</span>
                </div>

                <div class="material-title">{{ $material?->nama ?? 'Bahan Pangan' }}</div>

                <table class="lot-info-table">
                    <tr>
                        <td class="label">No. Lot:</td>
                        <td class="val">{{ $lot->lot_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pemasok:</td>
                        <td class="val">{{ $supplier?->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Waktu Terima:</td>
                        <td class="val">{{ $lot->received_date ? \Carbon\Carbon::parse($lot->received_date)->format('d-m-Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tgl Kedaluwarsa:</td>
                        <td class="val" style="color: #DC2626;">{{ $lot->expiry_date ? \Carbon\Carbon::parse($lot->expiry_date)->format('d-m-Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jumlah Masuk:</td>
                        <td class="val">{{ number_format($lot->initial_qty, 2, ',', '.') }} {{ $material?->satuan ?? 'kg' }}</td>
                    </tr>
                </table>

                <div class="barcode-visual-box">
                    {{-- Clean Barcode SVG Pattern --}}
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
                    <div class="barcode-number-text">{{ $lot->barcode }}</div>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #fff; border-radius: 12px;">
                <p>Belum ada barcode lot yang diterbitkan untuk dokumen ini.</p>
            </div>
        @endforelse
    </div>

</body>
</html>
