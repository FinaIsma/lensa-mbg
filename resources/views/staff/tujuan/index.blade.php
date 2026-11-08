@extends('layouts.staff')

@section('title', 'Data Tujuan Distribusi - Staff SPPG')

@section('content')
<div class="tujuan-container">
    {{-- Header Judul & Deskripsi --}}
    <div class="tujuan-header">
        <h1 class="tujuan-title">Data Tujuan Distribusi</h1>
        <p class="tujuan-subtitle">Kelola sekolah, posyandu, dan lokasi sasaran penerima manfaat Makan Bergizi Gratis (MBG).</p>
    </div>

    {{-- 3 Kartu Statistik Sesuai Pedoman --}}
    <div class="tujuan-stats-grid">
        {{-- Card 1: Total Tujuan --}}
        <div class="tujuan-stat-card">
            <div class="tujuan-icon-box icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <div class="tujuan-stat-content">
                <span class="tujuan-stat-label">Total Tujuan</span>
                <span class="tujuan-stat-value">{{ number_format($stats['total_tujuan'], 0, ',', '.') }}</span>
                <span class="tujuan-stat-sub">Lokasi Terdaftar</span>
            </div>
        </div>

        {{-- Card 2: Total Target Penerima --}}
        <div class="tujuan-stat-card">
            <div class="tujuan-icon-box icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="tujuan-stat-content">
                <span class="tujuan-stat-label">Total Target Penerima</span>
                <span class="tujuan-stat-value">{{ number_format($stats['total_penerima'], 0, ',', '.') }}</span>
                <span class="tujuan-stat-sub">Siswa / Anak Penerima</span>
            </div>
        </div>

        {{-- Card 3: Total Pengiriman Selesai --}}
        <div class="tujuan-stat-card">
            <div class="tujuan-icon-box icon-indigo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
            </div>
            <div class="tujuan-stat-content">
                <span class="tujuan-stat-label">Total Pengiriman</span>
                <span class="tujuan-stat-value">{{ number_format($stats['total_pengiriman'], 0, ',', '.') }}</span>
                <span class="tujuan-stat-sub">Distribusi Terealisasi</span>
            </div>
        </div>
    </div>

    {{-- Unified Table Card Container --}}
    <div class="tujuan-table-card">
        @php
            $filterAction = Route::has('staff.destinations.index') ? route('staff.destinations.index') : url('/staff/tujuan');
        @endphp
        <form method="GET" action="{{ $filterAction }}" class="tujuan-filter-bar">
            {{-- Search Box --}}
            <div class="tujuan-search-box">
                <svg class="tujuan-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama atau alamat tujuan..." class="tujuan-search-input">
            </div>

            <div class="tujuan-actions-right">
                {{-- Dropdown Tipe Filter --}}
                <div class="tujuan-select-wrapper">
                    <select name="tipe" class="tujuan-select" onchange="this.form.submit()">
                        <option value="" {{ in_array(strtolower($tipe), ['', 'semua', 'semua tipe'], true) ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="sekolah" {{ strtolower($tipe) === 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                        <option value="posyandu" {{ strtolower($tipe) === 'posyandu' ? 'selected' : '' }}>Posyandu</option>
                        <option value="lainnya" {{ strtolower($tipe) === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <svg class="tujuan-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Tombol + Tambah Tujuan --}}
                <button type="button" class="btn-tujuan-primary" onclick="openModalTambah()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Tujuan</span>
                </button>
            </div>
        </form>

        {{-- Tabel Data Tujuan --}}
        <div class="tujuan-table-wrapper">
            <table class="tujuan-table">
                <thead>
                    <tr>
                        <th style="min-width: 220px;">Nama Tujuan</th>
                        <th style="min-width: 120px;">Tipe</th>
                        <th style="min-width: 200px;">Alamat</th>
                        <th style="min-width: 150px; text-align: right;">Target Penerima</th>
                        <th style="min-width: 130px; text-align: center;">Pengiriman</th>
                        <th style="min-width: 160px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($destinations as $dest)
                        @php
                            $tipeLower = strtolower($dest->tipe ?? 'lainnya');
                            $badgeClass = 'badge-tipe-lainnya';
                            if ($tipeLower === 'sekolah') $badgeClass = 'badge-tipe-sekolah';
                            elseif ($tipeLower === 'posyandu') $badgeClass = 'badge-tipe-posyandu';
                        @endphp
                        <tr>
                            <td>
                                <div class="tujuan-name-cell">
                                    <a href="javascript:void(0)" class="tujuan-name-link" onclick="openDetailTujuan({{ $dest->id }})">
                                        {{ $dest->nama }}
                                    </a>
                                    <span class="tujuan-name-meta">ID #TJ-{{ str_pad($dest->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-tipe {{ $badgeClass }}">
                                    {{ $dest->tipe_label }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 13.5px; color: #475569;" title="{{ $dest->alamat }}">
                                    {{ $dest->alamat ? \Illuminate\Support\Str::limit($dest->alamat, 45) : '-' }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #071E49;">
                                {{ number_format($dest->jumlah_penerima, 0, ',', '.') }}
                                <span style="font-size: 12px; font-weight: 400; color: #64748B;">Porsi</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-count">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    {{ number_format($dest->jumlah_pengiriman ?? 0, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <div class="tujuan-actions-cell" style="justify-content: center;">
                                    <button type="button" class="btn-table-action btn-detail" onclick="openDetailTujuan({{ $dest->id }})" title="Lihat detail & riwayat pengiriman">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <span>Detail</span>
                                    </button>

                                    <button type="button" class="btn-table-action btn-edit" onclick="openModalEdit({{ $dest->id }}, @js($dest->nama), @js($dest->tipe), @js($dest->alamat), {{ $dest->jumlah_penerima }})" title="Ubah data tujuan">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                        <span>Ubah</span>
                                    </button>

                                    @if (($dest->jumlah_pengiriman ?? 0) === 0)
                                        <button type="button" class="btn-table-action btn-delete" onclick="confirmDelete({{ $dest->id }}, @js($dest->nama))" title="Hapus tujuan (hanya jika belum ada pengiriman)">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="tujuan-empty-state">
                                    <div class="tujuan-empty-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                    </div>
                                    <h4 class="tujuan-empty-title">Belum ada tujuan distribusi</h4>
                                    <p class="tujuan-empty-desc">
                                        @if ($q !== '' || $tipe !== '')
                                            Tidak ditemukan lokasi tujuan yang cocok dengan filter pencarian.
                                        @else
                                            Daftarkan sekolah, posyandu, atau lembaga penerima manfaat pertama untuk SPPG ini.
                                        @endif
                                    </p>
                                    <button type="button" class="btn-tujuan-primary" onclick="openModalTambah()">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>Tambah Tujuan Pertama</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($destinations->hasPages())
            <div class="tujuan-pagination-wrapper">
                <span style="font-size: 13px; color: #64748B;">
                    Menampilkan {{ $destinations->firstItem() }} sampai {{ $destinations->lastItem() }} dari {{ $destinations->total() }} data tujuan
                </span>
                <div>
                    {{ $destinations->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modals & Drawer --}}
@include('staff.tujuan.partials.modals')
@endsection

