<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Audit Trail - {{ $namaSppg }} - Lensa MBG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 11px;
            color: #1E293B;
            background-color: #F8FAFC;
            line-height: 1.4;
            padding-bottom: 40px;
        }

        /* Floating action bar (hidden during print) */
        .print-actions-bar {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: #0F172A;
            color: #FFFFFF;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .print-actions-title {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .print-actions-btns {
            display: flex;
            gap: 10px;
        }

        .btn-print {
            background-color: #2563EB;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s ease;
        }

        .btn-print:hover {
            background-color: #1D4ED8;
        }

        .btn-close {
            background-color: #334155;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-close:hover {
            background-color: #475569;
        }

        /* Printable sheet */
        .sheet {
            max-width: 1200px;
            margin: 20px auto;
            background: #FFFFFF;
            padding: 28px 32px;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            border: 1px solid #E2E8F0;
        }

        /* Header Kop */
        .kop-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0F172A;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .kop-logo {
            height: 48px;
            width: auto;
        }

        .kop-title h1 {
            font-size: 16px;
            font-weight: 700;
            color: #0F172A;
            letter-spacing: -0.2px;
            text-transform: uppercase;
        }

        .kop-title p {
            font-size: 10px;
            color: #64748B;
            font-weight: 500;
        }

        .kop-badge {
            text-align: right;
            font-size: 10px;
            color: #475569;
        }

        .kop-badge strong {
            display: block;
            font-size: 12px;
            color: #0F172A;
        }

        /* Meta Filter Box */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 18px;
        }

        .meta-item dt {
            font-size: 9.5px;
            font-weight: 600;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .meta-item dd {
            font-size: 11px;
            font-weight: 600;
            color: #0F172A;
            margin-top: 2px;
        }

        /* Table */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 24px;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #CBD5E1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        table.report-table thead th {
            background-color: #F1F5F9;
            color: #0F172A;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            font-size: 9px;
            white-space: nowrap;
        }

        table.report-table tr:nth-child(even) td {
            background-color: #FAFAFC;
        }

        .badge-tag {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-ok {
            background-color: #DCFCE7;
            color: #166534;
        }

        .badge-bad {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .badge-info {
            background-color: #E0F2FE;
            color: #0369A1;
        }

        .diff-box {
            font-size: 8.5px;
            line-height: 1.35;
        }

        .diff-label {
            font-weight: 600;
            color: #475569;
        }

        .diff-old {
            color: #991B1B;
        }

        .diff-new {
            color: #166534;
        }

        /* Sign-off section */
        .signoff-section {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }

        .signoff-box {
            width: 250px;
            text-align: center;
        }

        .signoff-box p {
            font-size: 10px;
            color: #475569;
        }

        .signoff-space {
            height: 60px;
        }

        .signoff-name {
            font-weight: 700;
            color: #0F172A;
            text-decoration: underline;
        }

        /* Print Media Overrides */
        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }

            .print-actions-bar {
                display: none !important;
            }

            .sheet {
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }

            table.report-table tr {
                page-break-inside: avoid;
            }

            table.report-table thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>

    <div class="print-actions-bar">
        <div class="print-actions-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            <span>Pratinjau Dokumen Audit Trail - {{ $namaSppg }}</span>
        </div>
        <div class="print-actions-btns">
            <button type="button" class="btn-print" onclick="window.print()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
            <button type="button" class="btn-close" onclick="window.close()">Tutup</button>
        </div>
    </div>

    <div class="sheet">
        <div class="kop-wrapper">
            <div class="kop-left">
                <img src="{{ asset('images/LogoMBG1.png') }}" alt="Logo Lensa MBG" class="kop-logo">
                <div class="kop-title">
                    <h1>Lensa MBG &bull; Sistem Transparansi &amp; Traceability</h1>
                    <p>Laporan Resmi Jejak Audit Sistem &bull; Badan Gizi Nasional (BGN)</p>
                </div>
            </div>
            <div class="kop-badge">
                <strong>DOKUMEN RESMI AUDIT</strong>
                <span>Dicetak: {{ $waktuCetak }}</span>
            </div>
        </div>

        @php
            $formatTanggal = fn ($tgl) => $tgl ? \Illuminate\Support\Carbon::parse($tgl)->locale('id')->translatedFormat('j M Y') : null;
            $periodeText = match (true) {
                $dari && $sampai => $formatTanggal($dari) . ' s/d ' . $formatTanggal($sampai),
                (bool) $dari     => 'Mulai ' . $formatTanggal($dari),
                (bool) $sampai   => 'Sampai ' . $formatTanggal($sampai),
                default          => 'Semua Periode',
            };
            $labelPeran = match ($selectedRole ?? null) {
                'admin' => 'Admin Sistem',
                'sppg' => 'SPPG',
                'kepala_sppg' => 'Kepala SPPG',
                'staff' => 'Staff',
                default => null,
            };
        @endphp

        <div class="meta-grid">
            <div class="meta-item">
                <dt>Unit SPPG</dt>
                <dd>{{ $namaSppg }}</dd>
            </div>
            <div class="meta-item">
                <dt>Periode Data</dt>
                <dd>{{ $periodeText }}</dd>
            </div>
            <div class="meta-item">
                <dt>Filter &amp; Modul</dt>
                <dd>
                    {{ $modul ?: 'Semua Modul' }}
                    @if ($isAdmin && $labelPeran)
                        &bull; {{ $labelPeran }}
                    @endif
                    {{ $q ? "(\"{$q}\")" : '' }}
                </dd>
            </div>
            <div class="meta-item">
                <dt>Dicetak Oleh</dt>
                <dd>{{ $dicetakOleh }}</dd>
            </div>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 28px; text-align: center;">No</th>
                    <th style="width: 95px;">Waktu (WIB)</th>
                    <th style="width: 75px;">Kode Audit</th>
                    @if ($isAdmin)
                        <th style="width: 110px;">Unit SPPG</th>
                    @endif
                    <th style="width: 110px;">Pengguna</th>
                    <th style="width: 75px;">Modul</th>
                    <th style="width: 100px;">Kode Data</th>
                    <th style="width: 105px;">Aktivitas</th>
                    <th>Perubahan Data (Lama &rarr; Baru)</th>
                    <th style="width: 140px;">Alasan Koreksi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $tampilVal = function ($v) {
                        if (is_null($v)) return 'null';
                        if (is_bool($v)) return $v ? 'Ya' : 'Tidak';
                        if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
                        return (string) $v;
                    };
                @endphp

                @forelse ($rows as $index => $row)
                    @php
                        $kor = $row->correction_request_id ? $koreksi->get($row->correction_request_id) : null;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $row->created_at->locale('id')->translatedFormat('d/m/Y H.i') }}</td>
                        <td><strong>{{ $row->kode_audit }}</strong></td>
                        @if ($isAdmin)
                            <td>{{ $row->nama_sppg ? 'SPPG'.str_pad((string)$row->id_sppg, 3, '0', STR_PAD_LEFT).' - '.$row->nama_sppg : '-' }}</td>
                        @endif
                        <td>
                            <div>{{ $row->actor_nama ?: '(tanpa nama)' }}</div>
                            <small style="color: #64748B;">{{ $row->actor_type === 'staff' ? 'Staff' : ($row->pelaku_role === 'admin' ? 'Admin' : 'Kepala SPPG') }}</small>
                        </td>
                        <td>{{ $row->modul }}</td>
                        <td><strong>{{ $row->kode_data }}</strong></td>
                        <td>
                            <span class="badge-tag badge-{{ $row->nada }}">{{ $row->aktivitas_label }}</span>
                        </td>
                        <td>
                            @if (! empty($row->old_values) || ! empty($row->new_values))
                                <div class="diff-box">
                                    @php
                                        $allKeys = array_unique(array_merge(array_keys($row->old_values ?? []), array_keys($row->new_values ?? [])));
                                    @endphp
                                    @foreach ($allKeys as $k)
                                        <div>
                                            <span class="diff-label">{{ \Illuminate\Support\Str::headline($k) }}:</span>
                                            <span class="diff-old">{{ $tampilVal($row->old_values[$k] ?? null) }}</span>
                                            &rarr;
                                            <span class="diff-new">{{ $tampilVal($row->new_values[$k] ?? null) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span style="color: #94A3B8;">-</span>
                            @endif
                        </td>
                        <td>{{ $kor->reason ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 10 : 9 }}" style="text-align: center; padding: 20px; color: #64748B;">
                            Tidak ada rekaman jejak audit yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="signoff-section">
            <div class="signoff-box">
                <p>Disahkan secara sistem pada:</p>
                <p><strong>{{ now()->locale('id')->translatedFormat('j F Y') }}</strong></p>
                <div class="signoff-space"></div>
                <p class="signoff-name">{{ $dicetakOleh }}</p>
                <p>{{ $isAdmin ? 'Administrator Lensa MBG' : 'Kepala Unit SPPG' }}</p>
            </div>
        </div>
    </div>

</body>
</html>
