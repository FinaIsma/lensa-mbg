<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction Request - Admin Sistem Lensa MBG</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/correction-request.css', 'resources/js/correction-request.js'])
</head>
<body>
@php
    $namaUser = \Illuminate\Support\Facades\Auth::user()->nama ?? 'Nanda';
    $inisial = mb_strtoupper(mb_substr(trim($namaUser), 0, 1)) ?: 'A';
    $tglPendek = fn ($ymd) => \Illuminate\Support\Carbon::parse($ymd)->locale('id')->translatedFormat('j M Y');
    $labelTanggal = match (true) {
        $dari && $sampai => $tglPendek($dari) . ' - ' . $tglPendek($sampai),
        (bool) $dari     => 'Mulai ' . $tglPendek($dari),
        (bool) $sampai   => 'Sampai ' . $tglPendek($sampai),
        default          => 'Pilih rentan tanggal',
    };
@endphp
    <div class="admin-container">
        @include('admin.partials.sidebar')

        <div class="main-wrapper">
            <header class="top-header">
                <div class="header-user-section">
                    <div class="avatar-circle" aria-hidden="true">{{ $inisial }}</div>
                    <div class="user-info">
                        <span class="user-name">{{ $namaUser }}</span>
                        <span class="user-role">Admin Sistem</span>
                    </div>
                </div>
            </header>

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
                    <h1 class="page-title">Correction Request</h1>
                    <p class="page-subtitle">Kelola dan pantau pengajuan koreksi dari data SPPG</p>
                </div>

                {{-- 4 Stat Cards --}}
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
                            <span class="stat-label">Total Pengajuan</span>
                            <span class="stat-value">{{ number_format($totalCount, 0, ',', '.') }}</span>
                            <span class="stat-subtext">Semua pengajuan</span>
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
                            <span class="stat-label">Menunggu Review</span>
                            <span class="stat-value">{{ number_format($pendingCount, 0, ',', '.') }}</span>
                            <span class="stat-subtext">Menunggu pemeriksaan</span>
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
                            <span class="stat-label">Disetujui</span>
                            <span class="stat-value">{{ number_format($approvedCount, 0, ',', '.') }}</span>
                            <span class="stat-subtext">Pengajuan disetujui</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon-wrapper icon-red">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Ditolak</span>
                            <span class="stat-value">{{ number_format($rejectedCount, 0, ',', '.') }}</span>
                            <span class="stat-subtext">Pengajuan ditolak</span>
                        </div>
                    </div>
                </div>

                {{-- Table Card --}}
                <div class="table-card">
                    <form action="{{ route('admin.correction') }}" method="GET" class="filter-bar-form" id="filterForm">
                        <div class="filter-controls-left">
                            {{-- Date Range Popover --}}
                            <div class="cr-date">
                                <button type="button" class="cr-date-btn" id="dateBtn" aria-haspopup="true" aria-expanded="false">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <span>{{ $labelTanggal }}</span>
                                </button>
                                <div class="cr-datepop" id="datePop">
                                    <label for="dari">Dari tanggal</label>
                                    <input type="date" id="dari" name="dari" value="{{ $dari }}">
                                    <label for="sampai">Sampai tanggal</label>
                                    <input type="date" id="sampai" name="sampai" value="{{ $sampai }}">
                                    <div class="act">
                                        <button type="button" class="cr-btn" id="dateClear">Kosongkan</button>
                                        <button type="submit" class="cr-btn main">Terapkan</button>
                                    </div>
                                </div>
                            </div>

                            {{-- SPPG Filter --}}
                            <div class="select-dropdown-box">
                                <select name="sppg" class="select-filter-input" onchange="this.form.submit()">
                                    <option value="">Semua SPPG</option>
                                    @foreach ($sppgs as $s)
                                        <option value="{{ $s->id_sppg }}" {{ request('sppg') == $s->id_sppg ? 'selected' : '' }}>
                                            SPPG{{ str_pad($s->id_sppg, 3, '0', STR_PAD_LEFT) }} - {{ $s->nama_sppg }}
                                        </option>
                                    @endforeach
                                </select>
                                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>

                            {{-- Jenis Data Filter --}}
                            <div class="select-dropdown-box">
                                <select name="jenis_data" class="select-filter-input" onchange="this.form.submit()">
                                    <option value="">Semua Jenis Data</option>
                                    @foreach ($daftarJenisData as $jd)
                                        <option value="{{ $jd }}" {{ request('jenis_data') === $jd ? 'selected' : '' }}>
                                            {{ $jd }}
                                        </option>
                                    @endforeach
                                </select>
                                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>

                            {{-- Status Filter --}}
                            <div class="select-dropdown-box">
                                <select name="status" class="select-filter-input" onchange="this.form.submit()">
                                    <option value="">Status</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Belum Diverifikasi</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Terverifikasi</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                        </div>

                        <div class="filter-controls-right">
                            <a href="{{ route('admin.correction') }}" class="btn-reset-filter">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                    <polyline points="3 3 3 8 8 8"></polyline>
                                </svg>
                                <span>Hapus Filter</span>
                            </a>
                        </div>
                    </form>

                    {{-- Data Table --}}
                    <div class="table-responsive">
                        <table class="correction-table">
                            <thead>
                                <tr>
                                    <th>ID Pengajuan</th>
                                    <th>ID SPPG</th>
                                    <th>Jenis Data</th>
                                    <th>Status</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr>
                                        <td><strong>{{ $row->kode_pengajuan }}</strong></td>
                                        <td>{{ $row->kode_sppg }}</td>
                                        <td>{{ $row->jenis_data }}</td>
                                        <td>
                                            <span class="status-badge {{ $row->status_badge_class }}">
                                                {{ $row->status_label }}
                                            </span>
                                        </td>
                                        <td>{{ $row->requested_at ? $row->requested_at->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                                        <td>
                                            <button type="button" class="btn-action-detail" data-detail="{{ $row->id }}">
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
                                        <td colspan="6" class="empty-state">
                                            Tidak ada data pengajuan koreksi yang sesuai dengan filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Footer --}}
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

    {{-- Detail Drawer Backdrop & Container --}}
    <div class="cr-backdrop" id="detailBackdrop"></div>
    <aside class="cr-drawer" id="detailDrawer" role="dialog" aria-modal="true" aria-label="Detail Pengajuan Koreksi" aria-hidden="true" tabindex="-1">
        <div class="cr-scroll" id="detailScroll">
            <div class="cr-head">
                <h2>Detail Pengajuan Koreksi</h2>
                <button type="button" class="cr-x" id="detailClose" aria-label="Tutup">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div id="detailBody"></div>
        </div>
    </aside>

    {{-- Modal Konfirmasi Setuju --}}
    <div class="cr-modal-backdrop" id="approveModal">
        <div class="cr-modal">
            <h3>Konfirmasi Persetujuan</h3>
            <p>Apakah Anda yakin ingin menyetujui pengajuan koreksi <strong id="approveTargetCode"></strong>?</p>
            <form id="approveForm" method="POST">
                @csrf
                <div class="cr-modal-actions">
                    <button type="button" class="cr-modal-btn" data-close-modal>Batal</button>
                    <button type="submit" class="cr-modal-btn primary">Ya, Setujui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tolak Pengajuan --}}
    <div class="cr-modal-backdrop" id="rejectModal">
        <div class="cr-modal">
            <h3>Tolak Pengajuan Koreksi</h3>
            <p>Masukkan alasan atau catatan penolakan untuk <strong id="rejectTargetCode"></strong>:</p>
            <form id="rejectForm" method="POST">
                @csrf
                <textarea name="note" placeholder="Tulis alasan penolakan..." required></textarea>
                <div class="cr-modal-actions">
                    <button type="button" class="cr-modal-btn" data-close-modal>Batal</button>
                    <button type="submit" class="cr-modal-btn danger">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Detail Templates for each row --}}
    @foreach ($rows as $row)
        @php
            $firstEvidence = $row->evidences->first();
            $evidenceUrl = $firstEvidence ? asset('storage/' . ltrim($firstEvidence->file_path, '/')) : '#';
            $evidenceName = $firstEvidence ? basename($firstEvidence->file_path) : 'Bukti_koreksi_' . strtolower($row->jenis_data) . '.pdf';
        @endphp
        <template id="detail-{{ $row->id }}">
            {{-- Entity Summary Header --}}
            <div class="cr-entity-header">
                <div class="cr-entity-left">
                    <div class="cr-entity-avatar" aria-hidden="true"></div>
                    <div>
                        <div class="cr-entity-title">{{ $row->kode_pengajuan }}</div>
                        <div class="cr-entity-subtitle">{{ $row->kode_sppg }} - {{ $row->sppg->nama_sppg ?? 'SPPG' }}</div>
                    </div>
                </div>
                <span class="status-badge {{ $row->status_badge_class }}">
                    {{ $row->status_label }}
                </span>
            </div>

            {{-- Card 1: Informasi Pengajuan --}}
            <h3 class="cr-section-title">Informasi Pengajuan</h3>
            <div class="cr-info-card">
                <div class="cr-kv-row">
                    <span class="cr-kv-label">ID Pengajuan</span>
                    <span class="cr-kv-value">{{ $row->kode_pengajuan }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">Jenis Data</span>
                    <span class="cr-kv-value">{{ $row->jenis_data }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">Tanggal Pengajuan</span>
                    <span class="cr-kv-value">{{ $row->requested_at ? $row->requested_at->locale('id')->translatedFormat('d F Y') : '-' }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">Pengaju</span>
                    <span class="cr-kv-value">{{ $row->requester->nama ?? 'Yu Ji-Min' }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">Reviewer</span>
                    <span class="cr-kv-value">{{ $row->reviewer->nama ?? '-' }}</span>
                </div>
            </div>

            {{-- Card 2: Informasi SPPG --}}
            <h3 class="cr-section-title">Informasi SPPG</h3>
            <div class="cr-info-card">
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                            <line x1="9" y1="22" x2="9" y2="22.01"></line>
                            <line x1="15" y1="22" x2="15" y2="22.01"></line>
                            <line x1="9" y1="6" x2="9" y2="6.01"></line>
                            <line x1="15" y1="6" x2="15" y2="6.01"></line>
                            <line x1="9" y1="10" x2="9" y2="10.01"></line>
                            <line x1="15" y1="10" x2="15" y2="10.01"></line>
                            <line x1="9" y1="14" x2="9" y2="14.01"></line>
                            <line x1="15" y1="14" x2="15" y2="14.01"></line>
                        </svg>
                        Nama SPPG
                    </span>
                    <span class="cr-kv-value">{{ $row->sppg->nama_sppg ?? 'SPPG Malang 01' }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <line x1="7" y1="8" x2="7.01" y2="8"></line>
                            <line x1="11" y1="8" x2="17" y2="8"></line>
                            <line x1="7" y1="12" x2="17" y2="12"></line>
                        </svg>
                        ID SPPG
                    </span>
                    <span class="cr-kv-value">{{ $row->kode_sppg }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Alamat Lengkap
                    </span>
                    <span class="cr-kv-value">{{ $row->sppg->alamat_sppg ?? 'Jl Bedali Lawang' }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                        Provinsi
                    </span>
                    <span class="cr-kv-value">{{ $row->sppg->provinsi ?? 'Jawa Timur' }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V7l8-4v18"></path>
                            <path d="M19 21V11l-6-4"></path>
                        </svg>
                        Kota/Kabupaten
                    </span>
                    <span class="cr-kv-value">{{ $row->sppg->kabupaten_kota ?? 'Kabupaten Malang' }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                        </svg>
                        Kecamatan
                    </span>
                    <span class="cr-kv-value">{{ $row->sppg->kecamatan ?? 'Lawang' }}</span>
                </div>
            </div>

            {{-- Card 3: Detail Koreksi --}}
            <h3 class="cr-section-title">Detail Koreksi</h3>
            <div class="cr-info-card">
                <div class="cr-kv-row">
                    <span class="cr-kv-label">ID Data</span>
                    <span class="cr-kv-value">{{ $row->target_data_display }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">Data Lama</span>
                    <span class="cr-kv-value">{{ $row->tampil_old_value }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">Data Baru</span>
                    <span class="cr-kv-value">{{ $row->tampil_new_value }}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">Alasan Koreksi</span>
                    <span class="cr-kv-value">{{ $row->reason }}</span>
                </div>
            </div>

            {{-- Card 4: Lampiran Bukti --}}
            <h3 class="cr-section-title">Detail Koreksi</h3>
            <div class="cr-info-card">
                <div class="cr-file-box">
                    <div class="cr-file-left">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        <span class="cr-file-name">{{ $evidenceName }}</span>
                    </div>
                    <a href="{{ $evidenceUrl }}" target="_blank" rel="noopener" class="btn-file-view">Lihat</a>
                </div>
            </div>

            {{-- Action Buttons Footer --}}
            @if ($row->status === 'pending')
                <div class="cr-drawer-footer">
                    <button type="button" class="btn-decision btn-decision-approve"
                            data-action-approve="{{ route('admin.correction.approve', $row->id) }}"
                            data-code="{{ $row->kode_pengajuan }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="16 10 11 15 8 12"></polyline>
                        </svg>
                        <span>Setuju</span>
                    </button>
                    <button type="button" class="btn-decision btn-decision-reject"
                            data-action-reject="{{ route('admin.correction.reject', $row->id) }}"
                            data-code="{{ $row->kode_pengajuan }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        <span>Tolak</span>
                    </button>
                </div>
            @elseif ($row->status === 'approved' || $row->status === 'applied')
                <div class="cr-reviewed-notice approved">
                    <strong>Telah Disetujui</strong>
                    <div>Oleh: {{ $row->reviewer->nama ?? 'Admin Sistem' }} &middot; {{ $row->reviewed_at ? $row->reviewed_at->locale('id')->translatedFormat('j M Y, H.i') : '-' }} WIB</div>
                    @if ($row->review_note)
                        <div style="margin-top: 4px; font-style: italic;">Catatan: {{ $row->review_note }}</div>
                    @endif
                </div>
            @elseif ($row->status === 'rejected')
                <div class="cr-reviewed-notice rejected">
                    <strong>Pengajuan Ditolak</strong>
                    <div>Oleh: {{ $row->reviewer->nama ?? 'Admin Sistem' }} &middot; {{ $row->reviewed_at ? $row->reviewed_at->locale('id')->translatedFormat('j M Y, H.i') : '-' }} WIB</div>
                    @if ($row->review_note)
                        <div style="margin-top: 4px; font-style: italic;">Alasan: {{ $row->review_note }}</div>
                    @endif
                </div>
            @endif
        </template>
    @endforeach

</body>
</html>
