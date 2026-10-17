<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - Kepala SPPG Lensa MBG</title>
    {{-- Memakai CSS yang sama dengan halaman SPPG Management (layout, header, kartu, tabel, filter, pagination). --}}
    @vite(['resources/css/admin_sppg.css'])

    <style>
        /* Hanya untuk bagian yang tidak ada di halaman SPPG: profil berinisial, filter tanggal, dan panel detail.
           Variabel diberi awalan --au- agar tidak bertabrakan dengan variabel di CSS lain. */
        :root {
            --au-primary: #071E49; --au-second: #042A70; --au-bg: #F1F5F9; --au-element: #D9D9D9; --au-white: #FFFFFF;
            --au-line: rgba(7, 30, 73, .12); --au-muted: rgba(7, 30, 73, .62);
            --au-ok: #1a7f4f; --au-ok-bg: #dff4e8; --au-bad: #b42318; --au-bad-bg: #fde7e4; --au-info-bg: rgba(4, 42, 112, .10);
        }

        /* Profil: lingkaran berwarna + 1 huruf inisial. Ukuran mengikuti .avatar-circle milik CSS yang sudah ada. */
        .avatar-circle { display: grid; place-items: center; background: var(--au-second); color: var(--au-white); font-weight: 600; line-height: 1; }

        /* Filter tanggal (tombol + popover kalender) */
        .au-date { position: relative; }
        .au-date-btn { text-align: left; cursor: pointer; min-width: 190px; }
        .au-datepop { display: none; position: absolute; top: calc(100% + 6px); left: 0; z-index: 20; width: 290px; background: var(--au-white); border: 1px solid var(--au-line); border-radius: 10px; padding: 16px; box-shadow: 0 12px 30px rgba(7, 30, 73, .16); }
        .au-datepop.open { display: block; }
        .au-datepop label { display: block; font-size: 12px; font-weight: 500; margin: 0 0 4px; color: var(--au-primary); }
        .au-datepop input[type=date] { width: 100%; height: 38px; border: 1px solid var(--au-line); border-radius: 6px; padding: 0 10px; font: inherit; color: var(--au-primary); margin-bottom: 12px; }
        .au-datepop .act { display: flex; gap: 8px; justify-content: flex-end; }
        .au-btn { height: 36px; padding: 0 16px; border-radius: 6px; font: inherit; cursor: pointer; border: 1px solid var(--au-line); background: var(--au-white); color: var(--au-primary); }
        .au-btn.main { background: var(--au-primary); border-color: var(--au-primary); color: var(--au-white); }

        tr.au-sel td { background: var(--au-bg); }

        /* Panel detail: menimpa halaman dari kanan (bukan bagian dari tata letak) */
        .au-backdrop { position: fixed; inset: 0; background: rgba(7, 30, 73, .38); opacity: 0; pointer-events: none; transition: opacity .2s; z-index: 1040; }
        .au-backdrop.open { opacity: 1; pointer-events: auto; }
        .au-drawer { position: fixed; top: 0; right: 0; height: 100vh; width: min(460px, 100%); background: var(--au-bg); color: var(--au-primary); z-index: 1050; overflow-y: auto; padding: 28px 24px 36px; border-radius: 16px 0 0 16px; box-shadow: -12px 0 40px rgba(7, 30, 73, .22); transform: translateX(105%); transition: transform .25s ease; outline: 0; font-size: 14px; }
        .au-drawer.open { transform: translateX(0); }
        .au-head { display: flex; justify-content: space-between; align-items: center; }
        .au-head h2 { margin: 0; font-size: 18px; font-weight: 600; }
        .au-x { width: 34px; height: 34px; border-radius: 50%; border: 0; background: var(--au-element); color: var(--au-primary); display: grid; place-items: center; cursor: pointer; }
        .au-x:hover { background: var(--au-primary); color: var(--au-white); }
        .au-meta { display: flex; justify-content: space-between; align-items: center; gap: 10px; font-size: 13px; margin: 8px 0 22px; }
        .au-badge { padding: 4px 12px; border-radius: 5px; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .au-badge.ok { background: var(--au-ok-bg); color: var(--au-ok); } .au-badge.bad { background: var(--au-bad-bg); color: var(--au-bad); } .au-badge.info { background: var(--au-info-bg); color: var(--au-second); }
        .au-drawer h3 { font-size: 14px; font-weight: 600; margin: 24px 0 10px; display: flex; justify-content: space-between; }
        .au-box { background: var(--au-white); border-radius: 10px; padding: 16px 18px; font-size: 12.5px; margin: 0; }
        .au-box + .au-box { margin-top: 12px; }
        .au-kv { display: flex; gap: 16px; padding: 5px 0; } .au-kv dt { width: 110px; flex: none; font-weight: 500; } .au-kv dd { margin: 0; }
        .au-box .lbl { font-weight: 600; margin-bottom: 6px; }
        .au-box .line { padding: 1px 0; word-break: break-word; }
        .au-bukti { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: var(--au-white); border: 1.5px dashed var(--au-primary); border-radius: 6px; padding: 12px; }
        .au-bukti img { width: 100%; height: 130px; object-fit: cover; border-radius: 6px; display: block; }
        .au-bukti .file { display: flex; align-items: center; justify-content: center; height: 130px; border-radius: 6px; background: var(--au-element); padding: 8px; text-align: center; font-size: 12px; word-break: break-all; color: var(--au-primary); }
    </style>
</head>
<body>
@php
    /** Nilai apa pun -> teks pendek untuk bagian "Perubahan Data". */
    $tampil = function ($v) {
        if (is_null($v)) return '-';
        if (is_bool($v)) return $v ? 'Ya' : 'Tidak';
        if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
        return (string) $v;
    };
    $inisial = mb_strtoupper(mb_substr(trim($namaUser), 0, 1)) ?: 'A';
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

        <!-- Main Workspace -->
        <div class="main-wrapper">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-user-section">
                    <div class="avatar-circle" aria-hidden="true">{{ $inisial }}</div>
                    <div class="user-info">
                        <span class="user-name">{{ $namaUser }}</span>
                        <span class="user-role">Kepala SPPG</span>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
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

                <!-- Page Header Title -->
                <div class="page-title-section">
                    <h1 class="page-title">Audit Trail</h1>
                    <p class="page-subtitle">Riwayat perubahan dan penelusuran data {{ $namaSppg }}</p>
                </div>

                <!-- 3 Stat Summary Cards -->
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

                <!-- Filter & Search Control Panel -->
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
                            <!-- Filter tanggal: pilih dari kalender -->
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

                    <!-- Audit Table -->
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

                    <!-- Pagination Footer -->
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

    <!-- Panel Detail Audit Trail (menimpa halaman). Isinya diambil dari <template> milik baris yang diklik. -->
    <div class="au-backdrop" id="detailBackdrop"></div>
    <aside class="au-drawer" id="detailDrawer" role="dialog" aria-modal="true" aria-label="Detail Audit Trail" aria-hidden="true" tabindex="-1">
        <div class="au-head">
            <h2>Detail Audit Trail</h2>
            <button type="button" class="au-x" id="detailClose" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div id="detailBody"></div>
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

    <script>
    (function () {
        var drawer = document.getElementById('detailDrawer');
        var body = document.getElementById('detailBody');
        var backdrop = document.getElementById('detailBackdrop');
        var activeRow = null;

        function openDetail(id, row) {
            var tpl = document.getElementById('detail-' + id);
            if (!tpl) return;
            body.innerHTML = '';
            body.appendChild(tpl.content.cloneNode(true));
            drawer.scrollTop = 0;
            drawer.classList.add('open');
            backdrop.classList.add('open');
            drawer.setAttribute('aria-hidden', 'false');
            if (activeRow) activeRow.classList.remove('au-sel');
            activeRow = row; if (row) row.classList.add('au-sel');
            drawer.focus();
        }
        function closeDetail() {
            drawer.classList.remove('open');
            backdrop.classList.remove('open');
            drawer.setAttribute('aria-hidden', 'true');
            if (activeRow) { activeRow.classList.remove('au-sel'); activeRow = null; }
        }
        document.querySelectorAll('[data-detail]').forEach(function (b) {
            b.addEventListener('click', function () { openDetail(b.getAttribute('data-detail'), b.closest('tr')); });
        });
        document.getElementById('detailClose').addEventListener('click', closeDetail);
        backdrop.addEventListener('click', closeDetail);

        // Filter tanggal (popover kalender)
        var dateBtn = document.getElementById('dateBtn');
        var datePop = document.getElementById('datePop');
        function toggleDate(open) {
            var show = typeof open === 'boolean' ? open : !datePop.classList.contains('open');
            datePop.classList.toggle('open', show);
            dateBtn.setAttribute('aria-expanded', show ? 'true' : 'false');
        }
        dateBtn.addEventListener('click', function (e) { e.stopPropagation(); toggleDate(); });
        datePop.addEventListener('click', function (e) { e.stopPropagation(); });
        document.addEventListener('click', function () { toggleDate(false); });
        document.getElementById('dateClear').addEventListener('click', function () {
            document.getElementById('dari').value = '';
            document.getElementById('sampai').value = '';
            document.getElementById('filterForm').submit();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { closeDetail(); toggleDate(false); }
        });
    })();
    </script>
</body>
</html>
