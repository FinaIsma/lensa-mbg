<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - Lensa MBG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/admin/audit-trail.css', 'resources/js/admin/audit-trail.js'])
</head>
<body>
@php
    $tampil = function ($v) {
        if (is_null($v)) return '-';
        if (is_bool($v)) return $v ? 'Ya' : 'Tidak';
        if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
        return (string) $v;
    };
    $tglPendek = fn ($ymd) => \Illuminate\Support\Carbon::parse($ymd)->locale('id')->translatedFormat('j M Y');
    $labelTanggal = match (true) {
        $dari && $sampai => $tglPendek($dari) . ' - ' . $tglPendek($sampai),
        (bool) $dari     => 'Mulai ' . $tglPendek($dari),
        (bool) $sampai   => 'Sampai ' . $tglPendek($sampai),
        default          => 'Semua Tanggal',
    };
@endphp
    <div class="admin-container">
        @include($sidebar)

        <div class="main-wrapper">
            @include('admin.partials.navbar')

            <main class="content-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <div class="page-title-section">
                    <h1 class="page-title">Audit Trail</h1>
                    <p class="page-subtitle">Riwayat perubahan dan penelusuran data {{ $namaSppg }}</p>
                </div>

                <div class="stat-cards-grid">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper icon-blue">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Total Aktivitas</span>
                            <span class="stat-value">{{ number_format($totalSemua, 0, ',', '.') }}</span>
                            <span class="stat-subtext">Aktivitas Tercatat</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon-wrapper icon-yellow">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <polyline points="3 3 3 8 8 8"></polyline>
                                <line x1="12" y1="7" x2="12" y2="12"></line>
                                <line x1="12" y1="12" x2="16" y2="14"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Hari Ini</span>
                            <span class="stat-value">{{ number_format($totalHariIni, 0, ',', '.') }}</span>
                            <span class="stat-subtext">Aktivitas Hari Ini</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon-wrapper icon-green">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Perubahan Data</span>
                            <span class="stat-value">{{ number_format($totalPerubahan, 0, ',', '.') }}</span>
                            <span class="stat-subtext">Perubahan Tercatat</span>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <form action="{{ route($routeName) }}" method="GET" class="filter-bar-form" id="filterForm">
                        <div class="search-input-box">
                            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Pengguna atau ID Data" class="input-search">
                        </div>

                        <div class="filter-actions-group">

                            <div class="select-dropdown-box au-date">
                                <button type="button" class="select-status-verif au-date-btn" id="dateBtn" aria-haspopup="true" aria-expanded="false">{{ $labelTanggal }}</button>
                                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                                <div class="au-datepop" id="datePop">
                                    <label for="dari">Dari tanggal</label>
                                    <input type="date" id="dari" name="dari" value="{{ $dari }}">
                                    <label for="sampai">Sampai tanggal</label>
                                    <input type="date" id="sampai" name="sampai" value="{{ $sampai }}">
                                    <div class="act">
                                        <button type="button" class="au-btn" id="dateClear">Kosongkan</button>
                                        <button type="submit" class="au-btn main">Terapkan</button>
                                    </div>
                                </div>
                            </div>

                            <div class="select-dropdown-box">
                                <select name="modul" class="select-status-verif" onchange="this.form.submit()">
                                    <option value="">Semua Modul</option>
                                    @foreach ($daftarModul as $nama)
                                        <option value="{{ $nama }}" {{ request('modul') === $nama ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>

                            <a href="{{ route($routeName) }}" class="btn-reset-filter">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                    <polyline points="3 3 3 8 8 8"></polyline>
                                </svg>
                                <span>Hapus Filter</span>
                            </a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="sppg-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Modul</th>
                                    <th>Aktivitas</th>
                                    <th>Data</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr>
                                        <td class="text-secondary">{{ $rows->firstItem() + $loop->index }}</td>
                                        <td class="text-secondary">{{ $row->created_at->locale('id')->translatedFormat('j M Y, H.i') }}</td>
                                        <td class="font-medium text-dark">{{ $row->pelaku }}</td>
                                        <td class="text-dark">{{ $row->modul }}</td>
                                        <td class="text-dark">{{ $row->aktivitas_label }}</td>
                                        <td class="font-medium text-dark">{{ $row->kode_data }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn-action btn-detail" data-detail="{{ $row->id }}">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                <span>Detail</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            Tidak ada aktivitas yang sesuai dengan kriteria filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-pagination-footer">
                        <div class="pagination-info">
                            Menampilkan {{ $rows->firstItem() ?? 0 }}-{{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }} data
                        </div>
                        <div class="pagination-links">
                            {{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="au-backdrop" id="detailBackdrop"></div>
    <aside class="au-drawer" id="detailDrawer" role="dialog" aria-modal="true" aria-label="Detail Audit Trail" aria-hidden="true" tabindex="-1">
        <div class="au-head">
            <h2>Detail Audit Trail</h2>
            <button type="button" class="au-x" id="detailClose" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div id="detailBody" class="au-scroll"></div>
    </aside>

    @foreach ($rows as $row)
        @php
            $kor = $row->correction_request_id ? $koreksi->get($row->correction_request_id) : null;
            $bkt = $row->correction_request_id ? ($bukti->get($row->correction_request_id) ?? collect()) : collect();
        @endphp
        <template id="detail-{{ $row->id }}">
            <div class="au-meta">
                <span>{{ $row->kode_audit }} &middot; Data {{ $row->modul }} {{ $row->kode_data }}</span>
                <span class="au-badge {{ $row->nada }}">{{ $row->aktivitas_label }}</span>
            </div>

            <h3>Informasi Aktivitas</h3>
            <dl class="au-box">
                <div class="au-kv"><dt>Pengguna</dt><dd>{{ $row->pelaku }}</dd></div>
                <div class="au-kv"><dt>Waktu</dt><dd>{{ $row->created_at->locale('id')->translatedFormat('j M Y, H.i') }} WIB</dd></div>
                <div class="au-kv"><dt>Modul</dt><dd>{{ $row->modul }}</dd></div>
            </dl>

            @if (! empty($row->old_values) || ! empty($row->new_values))
                <h3><span>Perubahan Data</span><span style="font-weight:500">{{ $row->kode_data }}</span></h3>
                @if (! empty($row->old_values))
                    <div class="au-box">
                        <div class="lbl">Data Lama</div>
                        @foreach ($row->old_values as $kolom => $nilai)
                            <div class="line">{{ \Illuminate\Support\Str::headline($kolom) }}: {{ $tampil($nilai) }}</div>
                        @endforeach
                    </div>
                @endif
                @if (! empty($row->new_values))
                    <div class="au-box">
                        <div class="lbl">Data Baru</div>
                        @foreach ($row->new_values as $kolom => $nilai)
                            <div class="line">{{ \Illuminate\Support\Str::headline($kolom) }}: {{ $tampil($nilai) }}</div>
                        @endforeach
                    </div>
                @endif
            @endif

            @if ($kor)
                <h3>Alasan Perubahan</h3>
                <div class="au-box" style="min-height:70px">{{ $kor->reason }}</div>

                @if ($bkt->isNotEmpty())
                    <h3>Bukti</h3>
                    <div class="au-bukti">
                        @foreach ($bkt as $b)
                            @php $url = asset('storage/' . ltrim($b->file_path, '/')); @endphp
                            <a href="{{ $url }}" target="_blank" rel="noopener">
                                @if (str_starts_with((string) $b->file_type, 'image/'))
                                    <img src="{{ $url }}" alt="{{ $b->description ?? 'Bukti' }}">
                                @else
                                    <div class="file">{{ basename($b->file_path) }}</div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif
        </template>
    @endforeach


</body>
</html>
