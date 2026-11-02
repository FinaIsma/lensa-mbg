<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembelian - {{ $purchase->purchase_no }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: #F8FAFC;
            color: #1E293B;
            padding: 30px;
        }
        .print-page {
            max-width: 820px;
            margin: 0 auto;
            background-color: #FFFFFF;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #071E49;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: 700;
            color: #071E49;
        }
        .brand-sub {
            font-size: 13px;
            color: #64748B;
            margin-top: 2px;
        }
        .doc-badge {
            text-align: right;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 700;
            color: #042A70;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-no {
            font-size: 13.5px;
            font-weight: 600;
            color: #334155;
            margin-top: 4px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
            font-size: 13.5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            width: 110px;
            font-weight: 600;
            color: #071E49;
            flex-shrink: 0;
        }
        .info-val {
            color: #334155;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .table-items th {
            background-color: #F1F5F9;
            color: #071E49;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 14px;
            text-align: left;
            border: 1px solid #E2E8F0;
        }
        .table-items td {
            padding: 12px 14px;
            font-size: 13px;
            border: 1px solid #E2E8F0;
            color: #334155;
        }
        .total-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .total-content {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 14px 24px;
            text-align: right;
            font-size: 16px;
            font-weight: 700;
            color: #071E49;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
            text-align: center;
        }
        .sig-role {
            font-size: 13px;
            font-weight: 600;
            color: #071E49;
            margin-bottom: 60px;
        }
        .sig-name {
            font-size: 13.5px;
            font-weight: 600;
            color: #1E293B;
            border-top: 1px dashed #94A3B8;
            padding-top: 8px;
            display: inline-block;
            min-width: 180px;
        }
        .action-no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            background-color: #042A70;
            color: #FFFFFF;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        @media print {
            body {
                background-color: #FFFFFF;
                padding: 0;
            }
            .print-page {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .action-no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="action-no-print">
        <button class="btn-print" onclick="window.print()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>Cetak Dokumen</span>
        </button>
    </div>

    <div class="print-page">
        {{-- Header SPPG Dinamis Sesuai Nama SPPG --}}
        <div class="print-header">
            <div>
                <div class="brand-title">{{ $purchase->sppg->nama_sppg ?? 'SPPG MBG' }}</div>
                <div class="brand-sub">
                    {{ $purchase->sppg->alamat_sppg ?? 'Sistem Manajemen Pengadaan Pangan MBG' }}
                    @if(!empty($purchase->sppg->no_telepon))
                        &bull; Telp: {{ $purchase->sppg->no_telepon }}
                    @endif
                </div>
            </div>
            <div class="doc-badge">
                <div class="doc-title">Nota Pembelian</div>
                <div class="doc-no">{{ $purchase->purchase_no }}</div>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <div class="info-row">
                    <span class="info-label">No. Nota:</span>
                    <span class="info-val"><strong>{{ $purchase->purchase_no }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal:</span>
                    <span class="info-val">{{ $purchase->purchase_date ? $purchase->purchase_date->format('d-m-Y') : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-val">{{ $purchase->status_label }}</span>
                </div>
            </div>

            <div>
                <div class="info-row">
                    <span class="info-label">Pemasok:</span>
                    <span class="info-val"><strong>{{ $purchase->supplier->nama ?? '-' }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Alamat:</span>
                    <span class="info-val">{{ $purchase->supplier->alamat ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dicatat Oleh:</span>
                    <span class="info-val">{{ $purchase->creator->nama ?? (auth('staff')->user()->nama ?? 'Staff SPPG') }}</span>
                </div>
            </div>
        </div>

        <table class="table-items">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama Bahan</th>
                    <th style="width: 120px;">Jumlah</th>
                    <th style="width: 140px;">Harga / Satuan</th>
                    <th style="width: 160px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td><strong>{{ $item->material->nama ?? 'Bahan' }}</strong></td>
                        <td>{{ rtrim(rtrim(number_format((float) $item->qty_ordered, 3, ',', '.'), '0'), ',') }} {{ $item->material->satuan ?? '' }}</td>
                        <td>{{ $item->formatted_unit_price }}</td>
                        <td style="text-align: right; font-weight: 600;">{{ $item->formatted_subtotal }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-box">
            <div class="total-content">
                Total Belanja : {{ $purchase->formatted_total_cost }}
            </div>
        </div>

        <div class="signatures">
            <div>
                <div class="sig-role">Staff Pengadaan {{ $purchase->sppg->nama_sppg ?? '' }}</div>
                <div class="sig-name">{{ $purchase->creator->nama ?? (auth('staff')->user()->nama ?? 'Staff Pengadaan') }}</div>
            </div>
            <div>
                <div class="sig-role">Pemasok / Toko</div>
                <div class="sig-name">{{ $purchase->supplier->nama ?? 'Pemasok Bahan' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
