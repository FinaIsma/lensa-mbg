@extends('layouts.kepala-sppg')

@section('title', 'Staff Manajemen')

@push('styles')
    @vite('resources/css/KepalaSPPG/staff-management.css')
@endpush

@section('content')
<div class="staff-page-container">
    @if (session('success'))
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
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

    @if ($errors->any())
        <div class="alert alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div style="display: flex; flex-direction: column; gap: 2px;">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="page-title-section">
        <h1 class="page-title">Staff Manajemen</h1>
        <p class="page-subtitle">Kelola data akun staff</p>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="stat-cards-grid">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Staff</span>
                <span class="stat-value">{{ number_format($totalStaff, 0, ',', '.') }}</span>
                <span class="stat-subtext">Staff Tercatat</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Status Aktif</span>
                <span class="stat-value">{{ number_format($activeStaff, 0, ',', '.') }}</span>
                <span class="stat-subtext">Staff Aktif</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <polyline points="3 3 3 8 8 8"></polyline>
                    <line x1="12" y1="7" x2="12" y2="12"></line>
                    <line x1="12" y1="12" x2="16" y2="14"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Belum Verifikasi</span>
                <span class="stat-value">{{ number_format($pendingStaff, 0, ',', '.') }}</span>
                <span class="stat-subtext">Menunggu Verifikasi</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper icon-red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Status Nonaktif</span>
                <span class="stat-value">{{ number_format($inactiveStaff, 0, ',', '.') }}</span>
                <span class="stat-subtext">Staff Nonaktif</span>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="table-card">
        <form action="{{ route('sppg.staff.index') }}" method="GET" class="filter-bar-form">
            <div class="search-input-box">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama, Email Staff SSPPG" class="input-search">
            </div>

            <div class="filter-actions-group">
                <div class="select-dropdown-box">
                    <select name="status_verif" class="select-status-verif" onchange="this.form.submit()">
                        <option value="semua" {{ !request('status_verif') || request('status_verif') === 'semua' ? 'selected' : '' }}>Status Verif</option>
                        <option value="aktif" {{ request('status_verif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="menunggu_persetujuan" {{ request('status_verif') === 'menunggu_persetujuan' ? 'selected' : '' }}>Belum Verifikasi</option>
                        <option value="nonaktif" {{ request('status_verif') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="ditolak" {{ request('status_verif') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                <button type="button" class="btn-tambah-staff" onclick="openTambahStaffModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Staff</span>
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>ID Staff</th>
                        <th>Staff</th>
                        <th>Email</th>
                        <th>No Telepon</th>
                        <th>Tanggal Dibuat</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staffs as $item)
                        @php
                            $staffIdFormatted = 'STF' . str_pad((string) $item->id, 3, '0', STR_PAD_LEFT);
                            $initial = mb_strtoupper(mb_substr(trim($item->nama ?? 'S'), 0, 1)) ?: 'S';
                            $bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            $tgl = $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at) : now();
                            $tglFormatted = $tgl->format('d') . ' ' . ($bulanIndo[(int) $tgl->format('n')] ?? $tgl->format('F')) . ' ' . $tgl->format('Y');

                            $sppgTgl = ($sppg && $sppg->created_at) ? \Illuminate\Support\Carbon::parse($sppg->created_at) : null;
                            $sppgTglFormatted = $sppgTgl ? ($sppgTgl->format('d') . ' ' . ($bulanIndo[(int) $sppgTgl->format('n')] ?? $sppgTgl->format('F')) . ' ' . $sppgTgl->format('Y')) : '-';

                            $itemDetailJson = json_encode([
                                'id' => $item->id,
                                'staff_id_formatted' => $staffIdFormatted,
                                'nama' => $item->nama,
                                'email' => $item->email,
                                'no_telepon' => $item->no_telepon ?? '-',
                                'status' => $item->status,
                                'sppg_nama' => $sppg->nama_sppg ?? '-',
                                'sppg_id_formatted' => 'SPPG' . str_pad((string) ($sppg->id_sppg ?? 1), 3, '0', STR_PAD_LEFT),
                                'alamat_sppg' => $sppg->alamat_sppg ?? '-',
                                'sppg_alamat' => $sppg->alamat_sppg ?? '-',
                                'sppg_provinsi' => $sppg->provinsi ?? '-',
                                'sppg_kabupaten' => $sppg->kabupaten_kota ?? '-',
                                'sppg_kecamatan' => $sppg->kecamatan ?? '-',
                                'tanggal_dibuat' => $tglFormatted,
                                'sppg_tanggal_dibuat' => $sppgTglFormatted,
                                'nama_kepala' => $sppg->user->nama ?? auth()->user()->nama ?? '-',
                            ]);
                        @endphp
                        <tr>
                            <td>
                                <span class="staff-id-text">{{ $staffIdFormatted }}</span>
                            </td>
                            <td>
                                <button type="button" class="staff-name-btn" onclick='openDetailModal({{ $itemDetailJson }})' title="Lihat detail {{ $item->nama }}">
                                    {{ $item->nama }}
                                </button>
                            </td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->no_telepon ?? '-' }}</td>
                            <td>
                                <span class="staff-date-text">{{ $tglFormatted }}</span>
                            </td>
                            <td>
                                @if ($item->status === 'aktif')
                                    <span class="badge-status badge-status-aktif">
                                        <span class="status-dot"></span> Aktif
                                    </span>
                                @elseif (in_array($item->status, ['menunggu', 'menunggu_persetujuan']))
                                    <span class="badge-status badge-status-belum">
                                        <span class="status-dot"></span> Belum Verifikasi
                                    </span>
                                @elseif ($item->status === 'ditolak')
                                    <span class="badge-status badge-status-ditolak">
                                        <span class="status-dot"></span> Ditolak
                                    </span>
                                @else
                                    <span class="badge-status badge-status-nonaktif">
                                        <span class="status-dot"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons-group">
                                    {{-- Aturan Aksi: jika aktif => Nonaktifkan (muncul pop-up), jika belum diverifikasi/nonaktif => Detail --}}
                                    @if ($item->status === 'aktif')
                                        <button type="button" class="btn-action btn-action-nonaktifkan" onclick='openNonaktifModal({{ $item->id }}, "{{ addslashes($item->nama) }}")'>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="8.5" cy="7" r="4"></circle>
                                                <line x1="18" y1="8" x2="23" y2="13"></line>
                                                <line x1="23" y1="8" x2="18" y2="13"></line>
                                            </svg>
                                            <span>Nonaktifkan</span>
                                        </button>
                                    @else
                                        <button type="button" class="btn-action btn-action-detail" onclick='openDetailModal({{ $itemDetailJson }})'>
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                            <span>Detail</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                Belum ada data staff yang sesuai dengan pencarian atau filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="table-pagination-footer">
            <div class="pagination-info">
                Menampilkan {{ $staffs->firstItem() ?? 0 }}-{{ $staffs->lastItem() ?? 0 }} dari {{ $staffs->total() }} data
            </div>
            <div class="pagination-controls">
                {{-- Previous Page Link --}}
                @if ($staffs->onFirstPage())
                    <span class="pagination-btn disabled">&lt;</span>
                @else
                    <a href="{{ $staffs->previousPageUrl() }}" class="pagination-btn">&lt;</a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($staffs->getUrlRange(1, $staffs->lastPage()) as $page => $url)
                    @if ($page == $staffs->currentPage())
                        <span class="pagination-btn active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($staffs->hasMorePages())
                    <a href="{{ $staffs->nextPageUrl() }}" class="pagination-btn">&gt;</a>
                @else
                    <span class="pagination-btn disabled">&gt;</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
{{-- Modal Tambah Staff --}}
<div class="modal-backdrop" id="tambahStaffModal">
    <div class="modal-dialog tambah-modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Staff</h3>
            <button type="button" class="btn-close" onclick="closeTambahStaffModal()" aria-label="Tutup modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form action="{{ route('sppg.staff.store') }}" method="POST" autocomplete="off">
            @csrf
            <div class="modal-body">
                <div class="modal-inner-card">
                    <h4 class="modal-inner-title">Informasi Staff SPPG</h4>

                    <div class="form-group-with-icon">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <label class="field-label" for="inputNamaStaff">Nama Lengkap</label>
                        <input type="text" id="inputNamaStaff" name="nama" class="field-input" placeholder="Masukkan nama staff" required autocomplete="off">
                    </div>

                    <div class="form-group-with-icon">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <label class="field-label" for="inputEmailStaff">Email</label>
                        <input type="email" id="inputEmailStaff" name="email" class="field-input" placeholder="Masukkan email" required autocomplete="off">
                    </div>

                    <div class="form-group-with-icon">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <label class="field-label" for="inputTeleponStaff">No Telepon</label>
                        <input type="tel" id="inputTeleponStaff" name="no_telepon" class="field-input" placeholder="Masukkan nomor telepon" required autocomplete="off">
                    </div>

                    <div class="form-group-with-icon">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <label class="field-label" for="inputPasswordStaff">Password</label>
                        <input type="password" id="inputPasswordStaff" name="password" class="field-input" placeholder="Masukkan password (min. 8 karakter)" required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="form-group-with-icon">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <label class="field-label" for="inputPasswordConfirmationStaff">Konfirmasi</label>
                        <input type="password" id="inputPasswordConfirmationStaff" name="password_confirmation" class="field-input" placeholder="Konfirmasi password" required minlength="8" autocomplete="new-password">
                    </div>
                </div>

                <div class="modal-notice-box">
                    <span class="modal-notice-title">Pastikan data sudah sesuai.</span>
                    <span class="modal-notice-desc">Penambahan data staff akan diverifikasi oleh Admin Sistem sebelum diterapkan.</span>
                </div>

                <div class="modal-footer" style="padding: 16px 0 0;">
                    <button type="button" class="btn-modal-cancel" onclick="closeTambahStaffModal()">Batal</button>
                    <button type="submit" class="btn-modal-submit-green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Ajukan Penambahan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Staff (Tata Letak Sesuai Detail SPPG Admin) --}}
<div class="modal-backdrop" id="detailStaffModal">
    <div class="modal-dialog detail-modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Detail Staff</h3>
            <button type="button" class="btn-close" onclick="closeDetailModal()" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="modal-body">
            {{-- Entity Header: Avatar + Nama + ID SPPG --}}
            <div class="cr-entity-header">
                <div class="cr-entity-left">
                    <div class="cr-entity-avatar" id="detailAvatar">K</div>
                    <div>
                        <div class="cr-entity-title" id="detailStaffName">Kim Min-jeong</div>
                        <div class="cr-entity-subtitle" id="detailSppgId">SPPG001</div>
                    </div>
                </div>
            </div>

            {{-- Card 1: Informasi Staff SPPG --}}
            <div class="cr-info-card">
                <h3 class="cr-card-title">Informasi Staff SPPG</h3>
                <div class="cr-card-body">
                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><line x1="15" y1="8" x2="17" y2="8"/><line x1="15" y1="12" x2="17" y2="12"/><line x1="7" y1="16" x2="17" y2="16"/>
                            </svg>
                            ID Staff
                        </span>
                        <span class="cr-kv-value" id="detailStaffId">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            Nama Lengkap
                        </span>
                        <span class="cr-kv-value" id="detailInfoNama">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            Email
                        </span>
                        <span class="cr-kv-value" id="detailInfoEmail">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            No Telepon
                        </span>
                        <span class="cr-kv-value" id="detailInfoTelepon">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>
                            </svg>
                            Tanggal Dibuat
                        </span>
                        <span class="cr-kv-value" id="detailInfoTgl">-</span>
                    </div>
                </div>
            </div>

            {{-- Card 2: Informasi Penempatan SPPG --}}
            <div class="cr-info-card">
                <h3 class="cr-card-title">Informasi Penempatan SPPG</h3>
                <div class="cr-card-body">
                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="22" x2="9" y2="18"/><line x1="15" y1="22" x2="15" y2="18"/><line x1="9" y1="18" x2="15" y2="18"/><line x1="8" y1="6" x2="8.01" y2="6"/><line x1="12" y1="6" x2="12.01" y2="6"/><line x1="16" y1="6" x2="16.01" y2="6"/><line x1="8" y1="10" x2="8.01" y2="10"/><line x1="12" y1="10" x2="12.01" y2="10"/><line x1="16" y1="10" x2="16.01" y2="10"/><line x1="8" y1="14" x2="8.01" y2="14"/><line x1="12" y1="14" x2="12.01" y2="14"/><line x1="16" y1="14" x2="16.01" y2="14"/>
                            </svg>
                            Nama SPPG
                        </span>
                        <span class="cr-kv-value" id="detailSppgNama">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><line x1="15" y1="8" x2="17" y2="8"/><line x1="15" y1="12" x2="17" y2="12"/><line x1="7" y1="16" x2="17" y2="16"/>
                            </svg>
                            ID SPPG
                        </span>
                        <span class="cr-kv-value" id="detailSppgIdDetail">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>
                            </svg>
                            Alamat Lengkap
                        </span>
                        <span class="cr-kv-value" id="detailSppgAlamat">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>
                            </svg>
                            Provinsi
                        </span>
                        <span class="cr-kv-value" id="detailSppgProvinsi">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>
                            </svg>
                            Kota/Kabupaten
                        </span>
                        <span class="cr-kv-value" id="detailSppgKabupaten">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 19 21 12 17 5 21 12 2"/>
                            </svg>
                            Kecamatan
                        </span>
                        <span class="cr-kv-value" id="detailSppgKecamatan">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>
                            </svg>
                            Tanggal dibuat
                        </span>
                        <span class="cr-kv-value" id="detailSppgTgl">-</span>
                    </div>

                    <div class="cr-kv-row">
                        <span class="cr-kv-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            Nama Kepala SPPG
                        </span>
                        <span class="cr-kv-value" id="detailSppgKepala">-</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Footer: Tombol Nonaktifkan jika staff aktif --}}
        <div class="modal-footer full-width-footer" id="detailModalFooter" style="display: none;">
            <button type="button" id="btnDetailDeactivate" class="btn-modal-action-deactivate" onclick="deactivateFromDetail()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="18" y1="8" x2="23" y2="13"></line>
                    <line x1="23" y1="8" x2="18" y2="13"></line>
                </svg>
                <span>Nonaktifkan</span>
            </button>
        </div>
    </div>
</div>

{{-- Modal Pop-up Konfirmasi Nonaktifkan Staff --}}
<div class="modal-backdrop" id="nonaktifStaffModal">
    <div class="modal-dialog" style="max-width: 460px;">
        <div class="modal-header">
            <h3 class="modal-title" style="color: #DC2626;">Nonaktifkan Akun Staff</h3>
            <button type="button" class="modal-close-btn" onclick="closeNonaktifModal()" aria-label="Tutup modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form id="nonaktifStaffForm" method="POST" action="">
            @csrf
            <div class="modal-body">
                <p style="font-size: 14px; color: #475569; margin: 0; line-height: 1.5;">
                    Apakah Anda yakin ingin menonaktifkan akun staff <strong id="nonaktifStaffName" style="color: #0F172A;">-</strong>? Staff yang dinonaktifkan tidak akan dapat mengakses sistem.
                </p>

                <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 12px;">
                    <label for="inputAlasanNonaktif" style="font-size: 13px; font-weight: 500; color: #334155;">Alasan Penonaktifan (opsional)</label>
                    <textarea id="inputAlasanNonaktif" name="reason" rows="2" style="border: 1px solid #CBD5E1; border-radius: 8px; padding: 10px; font-size: 13px; outline: none;" placeholder="Contoh: Mengundurkan diri / mutasi"></textarea>
                </div>

                <div class="modal-footer" style="padding: 16px 0 0; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-modal-cancel" onclick="closeNonaktifModal()">Batal</button>
                    <button type="submit" class="btn-action btn-action-nonaktifkan" style="padding: 10px 20px; font-size: 14px; background: #DC2626; color: #FFFFFF;">
                        Nonaktifkan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/KepalaSPPG/staff-management.js')
@endpush
