@extends('layouts.staff')

@section('title', 'Data Pemasok - Staff SPPG')

@section('content')
<div class="pemasok-container">
    {{-- Header Judul & Deskripsi --}}
    <div class="pemasok-header">
        <h1 class="pemasok-title">Data Pemasok</h1>
        <p class="pemasok-subtitle">Kelola data vendor penyedia bahan baku pangan untuk SPPG ini.</p>
    </div>

    {{-- 3 Kartu Statistik Sesuai Mockup --}}
    <div class="pemasok-stats-grid">
        {{-- Card 1: Total Pemasok --}}
        <div class="pemasok-stat-card">
            <div class="pemasok-icon-box icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="pemasok-stat-content">
                <span class="pemasok-stat-label">Total Pemasok</span>
                <span class="pemasok-stat-value">{{ number_format($stats['total_pemasok'], 0, ',', '.') }}</span>
                <span class="pemasok-stat-sub">Pemasok</span>
            </div>
        </div>

        {{-- Card 2: Status Aktif --}}
        <div class="pemasok-stat-card">
            <div class="pemasok-icon-box icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="pemasok-stat-content">
                <span class="pemasok-stat-label">Status Aktif</span>
                <span class="pemasok-stat-value">{{ number_format($stats['status_aktif'], 0, ',', '.') }}</span>
                <span class="pemasok-stat-sub">Pemasok Aktif</span>
            </div>
        </div>

        {{-- Card 3: Total Pembelian --}}
        <div class="pemasok-stat-card">
            <div class="pemasok-icon-box icon-indigo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M6 12h.01M18 12h.01"></path>
                </svg>
            </div>
            <div class="pemasok-stat-content">
                <span class="pemasok-stat-label">Total Pembelian</span>
                <span class="pemasok-stat-value">{{ $stats['formatted_total_pembelian'] }}</span>
                <span class="pemasok-stat-sub">&nbsp;</span>
            </div>
        </div>
    </div>

    {{-- Unified Table Card Container --}}
    <div class="pemasok-table-card">
        @php
            $filterAction = Route::has('staff.suppliers.index') ? route('staff.suppliers.index') : url('/staff/pemasok');
        @endphp
        <form method="GET" action="{{ $filterAction }}" class="pemasok-filter-bar">
            {{-- Search Box (Berdasarkan Nama Pemasok) --}}
            <div class="pemasok-search-box">
                <svg class="pemasok-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama pemasok" class="pemasok-search-input">
            </div>

            <div class="pemasok-actions-right">
                {{-- Dropdown Status Filter: Semua / Aktif / Nonaktif --}}
                <div class="pemasok-select-wrapper">
                    <select name="status" class="pemasok-select" onchange="this.form.submit()">
                        <option value="" {{ in_array(strtolower($status), ['', 'semua'], true) ? 'selected' : '' }}>Semua</option>
                        <option value="active" {{ in_array(strtolower($status), ['active', 'aktif'], true) ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ in_array(strtolower($status), ['inactive', 'nonaktif'], true) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    <svg class="pemasok-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Tombol + Tambah Pemasok --}}
                <button type="button" class="btn-tambah-pemasok" onclick="openModalTambahPemasok()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Pemasok</span>
                </button>
            </div>
        </form>

        {{-- Tabel Data Pemasok Sesuai Spesifikasi --}}
        <div class="pemasok-table-responsive">
            <table class="pemasok-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Nama</th>
                        <th style="width: 18%;">Kontak</th>
                        <th style="width: 14%;">Status</th>
                        <th style="width: 14%; text-align: center;">Jumlah pembelian</th>
                        <th style="width: 16%;">Total nilai</th>
                        <th style="width: 100px; text-align: center; white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $item)
                        <tr class="{{ strtolower($item->status) === 'inactive' ? 'row-pemasok-nonaktif' : '' }}">
                            <td>
                                <a href="javascript:void(0)" class="pemasok-name-link" onclick="openDetailPanel({{ $item->id }})" title="Klik untuk melihat panel detail">
                                    {{ $item->nama }}
                                </a>
                            </td>
                            <td style="color: #475569;">
                                {{ $item->kontak ?: '-' }}
                            </td>
                            <td>
                                @if (strtolower($item->status) === 'active')
                                    <span class="chip-status chip-aktif">
                                        <span class="chip-dot"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="chip-status chip-nonaktif">
                                        <span class="chip-dot"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 600; color: #0F172A;">
                                {{ $item->jumlah_pembelian ?? 0 }}
                            </td>
                            <td style="font-weight: 600; color: #0F172A;">
                                Rp {{ number_format($item->total_nilai ?? 0, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div class="pemasok-actions-group">
                                    {{-- Tombol Detail (Ikon Saja) --}}
                                    <button type="button" class="btn-pemasok-detail" onclick="openDetailPanel({{ $item->id }})" title="Lihat Detail Pemasok" aria-label="Lihat Detail Pemasok">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    {{-- Tombol Ubah (Ikon Saja) --}}
                                    <button type="button" class="btn-pemasok-edit" onclick="openModalEditPemasok({{ json_encode($item) }})" title="Ubah Data Pemasok" aria-label="Ubah Data Pemasok">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="pemasok-empty-state">
                                    <svg class="pemasok-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    @if(request('q') || (request('status') && !in_array(strtolower(request('status')), ['', 'semua'], true)))
                                        <p style="font-weight: 600; color: #334155; margin-bottom: 4px;">Pemasok tidak ditemukan</p>
                                        <p style="font-size: 13px; color: #94A3B8; margin-bottom: 12px;">Tidak ada pemasok yang cocok dengan kata kunci atau filter status.</p>
                                        <a href="{{ route('staff.suppliers.index') }}" style="font-size: 13px; color: #071E49; text-decoration: underline; font-weight: 600;">Lihat Semua Pemasok</a>
                                    @else
                                        <p style="font-weight: 600; color: #334155; font-size: 15px; margin-bottom: 10px;">
                                            Belum ada pemasok.
                                        </p>
                                        <button type="button" class="btn-tambah-pemasok" onclick="openModalTambahPemasok()" style="margin: 0 auto; display: inline-flex;">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                            <span>Tambahkan pemasok pertama</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer & Pagination Sesuai Mockup --}}
        <div class="pemasok-footer">
            <p class="pemasok-footer-text">
                Menampilkan {{ $suppliers->firstItem() ?? 0 }}-{{ $suppliers->lastItem() ?? 0 }} dari {{ $suppliers->total() }} data
            </p>

            @if ($suppliers->hasPages())
                <div class="pemasok-pagination">
                    {{-- Previous Page Link --}}
                    @if ($suppliers->onFirstPage())
                        <span class="pemasok-page-btn disabled" aria-disabled="true">&lt;</span>
                    @else
                        <a href="{{ $suppliers->previousPageUrl() }}" class="pemasok-page-btn" rel="prev">&lt;</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($suppliers->getUrlRange(1, $suppliers->lastPage()) as $page => $url)
                        @if ($page == $suppliers->currentPage())
                            <span class="pemasok-page-btn active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pemasok-page-btn">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($suppliers->hasMorePages())
                        <a href="{{ $suppliers->nextPageUrl() }}" class="pemasok-page-btn" rel="next">&gt;</a>
                    @else
                        <span class="pemasok-page-btn disabled" aria-disabled="true">&gt;</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@include('staff.pemasok.partials.modals')
@endsection

