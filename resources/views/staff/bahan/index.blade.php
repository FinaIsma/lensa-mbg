@extends('layouts.staff')

@section('title', 'Data Bahan Baku - Staff SPPG')

@section('content')
<div class="bahan-container">
    {{-- Header Judul & Deskripsi --}}
    <div class="bahan-header">
        <h1 class="bahan-title">Data Bahan Baku</h1>
        <p class="bahan-subtitle">Kelola data bahan baku pangan untuk SPPG ini</p>
    </div>

    {{-- 4 Kartu Statistik Ringkasan --}}
    <div class="bahan-stats-grid">
        {{-- Card 1: Total Bahan --}}
        <div class="bahan-stat-card">
            <div class="bahan-icon-box icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="bahan-stat-content">
                <span class="bahan-stat-label">Total Bahan</span>
                <span class="bahan-stat-value">{{ number_format($stats['total_bahan'], 0, ',', '.') }}</span>
                <span class="bahan-stat-sub">Jenis Bahan Terdaftar</span>
            </div>
        </div>

        {{-- Card 2: Stok Aman --}}
        <div class="bahan-stat-card">
            <div class="bahan-icon-box icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="bahan-stat-content">
                <span class="bahan-stat-label">Stok Aman</span>
                <span class="bahan-stat-value">{{ number_format($stats['stok_aman'], 0, ',', '.') }}</span>
                <span class="bahan-stat-sub">Stok Mencukupi</span>
            </div>
        </div>

        {{-- Card 3: Stok Menipis --}}
        <div class="bahan-stat-card">
            <div class="bahan-icon-box icon-amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="bahan-stat-content">
                <span class="bahan-stat-label">Stok Menipis</span>
                <span class="bahan-stat-value">{{ number_format($stats['stok_menipis'], 0, ',', '.') }}</span>
                <span class="bahan-stat-sub">Di Bawah Batas Minimum</span>
            </div>
        </div>

        {{-- Card 4: Stok Habis --}}
        <div class="bahan-stat-card">
            <div class="bahan-icon-box icon-red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <div class="bahan-stat-content">
                <span class="bahan-stat-label">Stok Habis</span>
                <span class="bahan-stat-value">{{ number_format($stats['stok_habis'], 0, ',', '.') }}</span>
                <span class="bahan-stat-sub">Stok Kosong</span>
            </div>
        </div>
    </div>

    {{-- Unified Table Card Container --}}
    <div class="bahan-table-card">
        @php
            $filterAction = Route::has('staff.materials.index') ? route('staff.materials.index') : url('/staff/bahan');
        @endphp
        <form method="GET" action="{{ $filterAction }}" class="bahan-filter-bar">
            {{-- Search Input (Cari berdasarkan kode atau nama bahan) --}}
            <div class="bahan-search-box">
                <svg class="bahan-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode atau nama bahan" class="bahan-search-input">
            </div>

            <div class="bahan-actions-right">
                {{-- Dropdown Filter Kategori --}}
                <div class="bahan-select-wrapper">
                    <select name="kategori" class="bahan-select" onchange="this.form.submit()">
                        <option value="" {{ in_array(strtolower($kategori), ['', 'semua', 'semua kategori'], true) ? 'selected' : '' }}>Semua Kategori</option>
                        @foreach ($availableCategories as $cat)
                            <option value="{{ $cat }}" {{ strcasecmp($kategori, $cat) === 0 ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                    <svg class="bahan-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Dropdown Filter Indikator Status --}}
                <div class="bahan-select-wrapper">
                    <select name="indikator" class="bahan-select" onchange="this.form.submit()">
                        <option value="" {{ in_array(strtolower($indikator), ['', 'semua', 'semua status', 'semua indikator'], true) ? 'selected' : '' }}>Semua Status</option>
                        <option value="aman" {{ strtolower($indikator) === 'aman' ? 'selected' : '' }}>Aman</option>
                        <option value="di_bawah_minimum" {{ in_array(strtolower($indikator), ['di_bawah_minimum', 'di bawah minimum', 'menipis', 'habis'], true) ? 'selected' : '' }}>Di bawah minimum</option>
                    </select>
                    <svg class="bahan-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Tombol + Tambah Bahan --}}
                <button type="button" class="btn-tambah-bahan" onclick="openModalTambahBahan()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Bahan</span>
                </button>
            </div>
        </form>

        {{-- Tabel Data Bahan Baku Sesuai Spesifikasi (Kode, Nama, Kategori, Satuan, Stok total, Stok minimum, Indikator, Aktif, Aksi) --}}
        <div class="bahan-table-responsive">
            <table class="bahan-table">
                <thead>
                    <tr>
                        <th style="width: 9%;">Kode</th>
                        <th style="width: 22%;">Nama</th>
                        <th style="width: 15%;">Kategori</th>
                        <th style="width: 8%;">Satuan</th>
                        <th style="width: 12%;">Stok total</th>
                        <th style="width: 12%;">Stok minimum</th>
                        <th style="width: 14%;">Indikator</th>
                        <th style="width: 10%;">Aktif</th>
                        <th style="width: 90px; text-align: center; white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materials as $item)
                        @php
                            $totalStok = (float) $item->total_stok;
                            $minStock = (float) $item->min_stock;
                            $isAman = $totalStok >= $minStock;

                            $formattedMinStock = (float) $minStock == (int) $minStock ? (int) $minStock : rtrim(rtrim(number_format($minStock, 3, ',', '.'), '0'), ',');
                            $formattedTotalStok = (float) $totalStok == (int) $totalStok ? (int) $totalStok : rtrim(rtrim(number_format($totalStok, 3, ',', '.'), '0'), ',');
                        @endphp
                        <tr class="{{ ! $item->is_active ? 'row-bahan-nonaktif' : '' }}">
                            {{-- Kode --}}
                            <td style="font-weight: 700; color: #071E49;">
                                {{ $item->kode }}
                            </td>

                            {{-- Nama Bahan (Dapat diklik ke panel Detail) --}}
                            <td>
                                <a href="javascript:void(0)" class="bahan-nama-link" onclick="openDetailPanelBahan({{ $item->id }})" title="Klik untuk melihat detail bahan">
                                    {{ $item->nama }}
                                </a>
                            </td>

                            {{-- Kategori --}}
                            <td style="color: #475569;">
                                {{ $item->kategori ?: '-' }}
                            </td>

                            {{-- Satuan --}}
                            <td style="color: #475569;">
                                {{ $item->satuan }}
                            </td>

                            {{-- Stok total (current_qty lot available yang belum kedaluwarsa) --}}
                            <td style="font-weight: 600; color: #0F172A;">
                                {{ $formattedTotalStok }} {{ $item->satuan }}
                            </td>

                            {{-- Stok minimum --}}
                            <td style="font-weight: 500; color: #334155;">
                                {{ $formattedMinStock }} {{ $item->satuan }}
                            </td>

                            {{-- Indikator (Chip hijau 'Aman' atau merah 'Di bawah minimum') --}}
                            <td>
                                @if ($isAman)
                                    <span class="chip-status chip-aman">
                                        <span class="chip-dot"></span>
                                        Aman
                                    </span>
                                @else
                                    <span class="chip-status chip-di-bawah-min">
                                        <span class="chip-dot"></span>
                                        Di bawah minimum
                                    </span>
                                @endif
                            </td>

                            {{-- Aktif (Chip aktif atau nonaktif) --}}
                            <td>
                                @if ($item->is_active)
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

                            {{-- Aksi (Detail, Ubah) --}}
                            <td style="text-align: center; white-space: nowrap;">
                                <div class="bahan-actions-group">
                                    {{-- Tombol Detail (Ikon Mata) --}}
                                    <button type="button"
                                        class="btn-bahan-detail"
                                        onclick="openDetailPanelBahan({{ $item->id }})"
                                        title="Detail Bahan"
                                        aria-label="Detail Bahan">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    {{-- Tombol Ubah (Ikon Pensil) --}}
                                    <button type="button"
                                        class="btn-bahan-edit"
                                        onclick="openModalEditBahan({{ $item->id }})"
                                        data-id="{{ $item->id }}"
                                        data-kode="{{ $item->kode }}"
                                        data-nama="{{ $item->nama }}"
                                        data-kategori="{{ $item->kategori }}"
                                        data-satuan="{{ $item->satuan }}"
                                        data-min-stock="{{ $minStock }}"
                                        data-is-active="{{ $item->is_active ? 1 : 0 }}"
                                        title="Ubah Data Bahan"
                                        aria-label="Ubah Data Bahan">
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
                            <td colspan="9">
                                <div class="bahan-empty-state">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                    </svg>
                                    @if(request('q') || request('kategori') || request('indikator'))
                                        <p style="font-weight: 600; color: #334155; margin-bottom: 4px;">Bahan baku tidak ditemukan</p>
                                        <p style="font-size: 13px; color: #94A3B8; margin-bottom: 12px;">Tidak ada bahan yang cocok dengan kriteria pencarian atau filter Anda.</p>
                                        <a href="{{ route('staff.materials.index') }}" style="font-size: 13px; color: #071E49; text-decoration: underline; font-weight: 600;">Lihat Semua Bahan</a>
                                    @else
                                        <p style="font-weight: 600; color: #334155; font-size: 15px; margin-bottom: 10px;">
                                            Belum ada bahan baku.
                                        </p>
                                        <button type="button" class="btn-tambah-bahan" onclick="openModalTambahBahan()" style="margin: 0 auto; display: inline-flex;">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                            <span>Tambahkan bahan pertama</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer & Pagination --}}
        <div class="bahan-footer">
            <p class="bahan-footer-text">
                Menampilkan {{ $materials->firstItem() ?? 0 }}-{{ $materials->lastItem() ?? 0 }} dari {{ $materials->total() }} data
            </p>

            @if ($materials->hasPages())
                <div class="bahan-pagination">
                    {{-- Previous Page Link --}}
                    @if ($materials->onFirstPage())
                        <span class="bahan-page-btn disabled" aria-disabled="true">&lt;</span>
                    @else
                        <a href="{{ $materials->previousPageUrl() }}" class="bahan-page-btn" rel="prev">&lt;</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($materials->getUrlRange(1, $materials->lastPage()) as $page => $url)
                        @if ($page == $materials->currentPage())
                            <span class="bahan-page-btn active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="bahan-page-btn">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($materials->hasMorePages())
                        <a href="{{ $materials->nextPageUrl() }}" class="bahan-page-btn" rel="next">&gt;</a>
                    @else
                        <span class="bahan-page-btn disabled" aria-disabled="true">&gt;</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@include('staff.bahan.partials.modals')
@endsection
