@extends('layouts.staff')

@section('title', 'Riwayat Stok Bahan - Staff SPPG')

@section('content')
<div class="space-y-6 pb-8">
    {{-- Page Title Section Sesuai Format Standar --}}
    <div class="page-title-section">
        <h1 class="page-title">Riwayat Stok Bahan</h1>
        <p class="page-subtitle">Buku catatan mutasi fisik bahan pangan masuk dan keluar secara otomatis untuk SPPG ini.</p>
    </div>

    {{-- 4 Stat Cards Grid Sesuai Format Standar (Mockup Pembelian) --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Total Masuk (IN) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <polyline points="19 12 12 19 5 12"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Masuk</span>
                <span class="stat-value">+{{ number_format($stats['total_in'], 2, ',', '.') }}</span>
                <span class="stat-subtext">Penerimaan & Koreksi</span>
            </div>
        </div>

        {{-- Card 2: Total Keluar (OUT) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="19" x2="12" y2="5"></line>
                    <polyline points="5 12 12 5 19 12"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Keluar</span>
                <span class="stat-value">-{{ number_format($stats['total_out'], 2, ',', '.') }}</span>
                <span class="stat-subtext">Produksi & Limbah</span>
            </div>
        </div>

        {{-- Card 3: Selisih Mutasi --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M8 12h8"></path>
                    @if($stats['net_diff'] > 0)
                        <path d="M12 8v8"></path>
                    @endif
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Selisih Mutasi</span>
                <span class="stat-value">{{ $stats['net_diff'] >= 0 ? '+' : '' }}{{ number_format($stats['net_diff'], 2, ',', '.') }}</span>
                <span class="stat-subtext">Akumulasi Saldo</span>
            </div>
        </div>

        {{-- Card 4: Total Catatan Transaksi --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Mutasi</span>
                <span class="stat-value">{{ number_format($stats['total_transaksi'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Catatan Transaksi</span>
            </div>
        </div>
    </div>

    {{-- Unified Table Card: Filter Bar + Table Bersatu Sesuai Format Standar --}}
    <div class="table-card">
        <form method="GET" action="{{ route('staff.riwayat.index') }}" class="filter-bar-form">
            {{-- Search Box --}}
            <div class="search-input-box riwayat-search-box">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari bahan, barcode, nomor lot..." class="input-search">
            </div>

            <div class="filter-actions-group">
                {{-- Dropdown Arah Mutasi --}}
                <div class="select-dropdown-box riwayat-select-box">
                    <select name="direction" class="select-status-verif riwayat-select js-auto-submit">
                        <option value="">Semua Arah</option>
                        <option value="IN" {{ $direction === 'IN' ? 'selected' : '' }}>Masuk (IN)</option>
                        <option value="OUT" {{ $direction === 'OUT' ? 'selected' : '' }}>Keluar (OUT)</option>
                    </select>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Dropdown Bahan Pangan --}}
                <div class="select-dropdown-box riwayat-select-box">
                    <select name="material_id" class="select-status-verif riwayat-select js-auto-submit">
                        <option value="">Semua Bahan</option>
                        @foreach($materials as $mat)
                            <option value="{{ $mat->id }}" {{ (string)$materialId === (string)$mat->id ? 'selected' : '' }}>
                                {{ $mat->nama }}
                            </option>
                        @endforeach
                    </select>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Dropdown Jenis Mutasi --}}
                <div class="select-dropdown-box riwayat-select-box">
                    <select name="movement_type" class="select-status-verif riwayat-select js-auto-submit">
                        <option value="">Semua Jenis</option>
                        <option value="RECEIVE_IN" {{ $movementType === 'RECEIVE_IN' ? 'selected' : '' }}>Penerimaan</option>
                        <option value="PRODUCTION_OUT" {{ $movementType === 'PRODUCTION_OUT' ? 'selected' : '' }}>Produksi</option>
                        <option value="DISPOSAL_OUT" {{ $movementType === 'DISPOSAL_OUT' ? 'selected' : '' }}>Limbah</option>
                        <option value="CORRECTION_IN" {{ $movementType === 'CORRECTION_IN' ? 'selected' : '' }}>Koreksi Masuk</option>
                        <option value="CORRECTION_OUT" {{ $movementType === 'CORRECTION_OUT' ? 'selected' : '' }}>Koreksi Keluar</option>
                        <option value="VOID_REVERSAL" {{ $movementType === 'VOID_REVERSAL' ? 'selected' : '' }}>Pembalikan (Void)</option>
                    </select>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                @if(!empty($q) || !empty($direction) || !empty($materialId) || !empty($movementType) || !empty($lotId) || !empty($startDate) || !empty($endDate))
                    <a href="{{ route('staff.riwayat.index') }}" class="btn-reset-filter" title="Reset filter">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>

        {{-- Table Container --}}
        <div class="table-responsive">
            <table class="staff-table data-table riwayat-table">
                <thead>
                    <tr>
                        <th class="riwayat-th">Waktu</th>
                        <th class="riwayat-th-material">Bahan Pangan</th>
                        <th class="riwayat-th">Barcode Lot</th>
                        <th class="riwayat-th">Jenis Mutasi</th>
                        <th class="riwayat-th">Arah</th>
                        <th class="riwayat-th">Jumlah</th>
                        <th class="riwayat-th">Petugas</th>
                        <th class="riwayat-th-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $m)
                        @php
                            $lot = $m->lot;
                            $material = $lot?->material;
                            $docRef = $m->doc_ref;

                            $detailPayload = [
                                'waktu' => $m->created_at ? $m->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') . ' WIB' : '-',
                                'direction' => $m->direction,
                                'direction_label' => $m->direction === 'IN' ? 'Masuk' : 'Keluar',
                                'direction_class' => $m->direction === 'IN' ? 'badge-received' : 'badge-cancelled',
                                'movement_type_label' => $m->movement_type_label,
                                'qty_formatted' => ($m->direction === 'IN' ? '+' : '-') . number_format((float)$m->qty, 2, ',', '.') . ' ' . ($material?->satuan ?? ''),
                                'petugas' => $m->creator?->nama ?? 'Sistem',
                                'material_nama' => $material?->nama ?? '-',
                                'material_kategori' => $material?->kategori ?? '-',
                                'material_satuan' => $material?->satuan ?? '-',
                                'barcode' => $lot?->barcode ?? '-',
                                'lot_number' => $lot?->lot_number ?? '-',
                                'current_qty' => $lot ? number_format((float)$lot->current_qty, 2, ',', '.') . ' ' . ($material?->satuan ?? '') : '-',
                                'expiry_date' => $lot?->expiry_date ? $lot->expiry_date->format('d-m-Y') : '-',
                                'supplier' => $lot?->supplier?->nama ?? '-',
                                'doc_label' => $docRef['label'] ?? 'Dokumen Mutasi',
                                'doc_code' => $docRef['code'] ?? '-',
                                'doc_url' => $docRef['url'] ?? null,
                            ];
                        @endphp
                        <tr>
                            {{-- Waktu --}}
                            <td class="riwayat-cell-time">
                                {{ $m->created_at ? $m->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-' }}
                            </td>

                            {{-- Bahan Pangan (Hanya Nama Bersih) --}}
                            <td class="riwayat-cell-primary">
                                {{ $material?->nama ?? '-' }}
                            </td>

                            {{-- Barcode / Lot (Hanya Barcode Bersih) --}}
                            <td class="riwayat-cell-primary">
                                {{ $lot?->barcode ?? '-' }}
                            </td>

                            {{-- Jenis Mutasi --}}
                            <td class="riwayat-cell-text">
                                {{ $m->movement_type_label }}
                            </td>

                            {{-- Arah --}}
                            <td class="riwayat-cell-badge">
                                <span class="badge-status {{ $m->direction === 'IN' ? 'badge-received' : 'badge-cancelled' }}">
                                    <span class="status-dot"></span>
                                    <span>{{ $m->direction === 'IN' ? 'Masuk' : 'Keluar' }}</span>
                                </span>
                            </td>

                            {{-- Jumlah --}}
                            <td class="riwayat-cell-qty {{ $m->direction === 'IN' ? 'riwayat-qty-in' : 'riwayat-qty-out' }}">
                                {{ $m->direction === 'IN' ? '+' : '-' }}{{ number_format((float)$m->qty, 2, ',', '.') }} {{ $material?->satuan ?? '' }}
                            </td>

                            {{-- Petugas --}}
                            <td class="riwayat-cell-text">
                                {{ $m->creator?->nama ?? 'Sistem' }}
                            </td>

                            {{-- Aksi: Tombol Mata untuk Melihat Right Drawer Detail --}}
                            <td class="riwayat-cell-center">
                                <div class="action-buttons-group">
                                    <button type="button" 
                                            class="btn-action-icon js-btn-movement-detail" 
                                            data-movement="{{ json_encode($detailPayload) }}" 
                                            title="Lihat Rincian Mutasi & Dokumen">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="riwayat-empty-state">
                                    <svg class="riwayat-empty-icon" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.6">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <p class="riwayat-empty-title">
                                        Belum ada catatan mutasi bahan
                                    </p>
                                    <p class="riwayat-empty-desc">
                                        Semua pergerakan bahan masuk dan keluar akan dicatat secara otomatis di sini.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Footer Sesuai Format Standar Mockup --}}
        @if($movements->hasPages() || $movements->total() > 0)
            <div class="table-pagination-footer">
                <div class="pagination-info">
                    Menampilkan {{ $movements->firstItem() ?? 0 }}-{{ $movements->lastItem() ?? 0 }} dari {{ $movements->total() }} data
                </div>
                <div class="pagination-links">
                    {{ $movements->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('modals')
{{-- ==========================================================================
     POPUP DETAIL MUTASI STOK: RIGHT DRAWER (PERSIS PEMBELIAN BAHAN)
     ========================================================================== --}}
<div class="purchase-drawer-backdrop" id="movementDrawerBackdrop"></div>
<aside class="purchase-drawer" id="movementDetailDrawer" role="dialog" aria-modal="true" aria-label="Detail Mutasi Stok">
    {{-- Header Drawer --}}
    <div class="purchase-drawer-header">
        <div>
            <h2 class="purchase-drawer-title">Detail Mutasi Stok</h2>
            <p class="purchase-drawer-subtitle">Informasi lengkap pergerakan bahan pangan dan dokumen transaksi terkait.</p>
        </div>
        <button type="button" class="modal-close-corner js-btn-close-drawer" aria-label="Tutup" title="Tutup">
            &times;
        </button>
    </div>

    {{-- Body Drawer (Scrollable) --}}
    <div class="purchase-drawer-scroll">
        {{-- Card 1: Informasi Mutasi --}}
        <div class="detail-card">
            <div class="riwayat-drawer-card-header">
                <h3 class="detail-card-title mb-0">Informasi Mutasi</h3>
                <span class="badge-status" id="drawerDirectionBadge">
                    <span class="status-dot"></span>
                    <span id="drawerDirectionText">-</span>
                </span>
            </div>

            <div class="detail-grid-2col">
                <div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Waktu :</span>
                        <span class="detail-item-value riwayat-value-bold" id="drawerWaktu">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Jenis Mutasi :</span>
                        <span class="detail-item-value" id="drawerJenisMutasi">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Jumlah :</span>
                        <span class="detail-item-value riwayat-cell-qty" id="drawerJumlah">-</span>
                    </div>
                </div>
                <div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Dicatat Oleh :</span>
                        <span class="detail-item-value" id="drawerPetugas">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Arah Aliran :</span>
                        <span class="detail-item-value" id="drawerArahLengkap">-</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Rincian Bahan & Lot Terkait --}}
        <div class="detail-card">
            <h3 class="detail-card-title">Rincian Bahan & Lot</h3>
            <div class="detail-grid-2col">
                <div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Nama Bahan :</span>
                        <span class="detail-item-value riwayat-value-bold" id="drawerNamaBahan">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Kategori :</span>
                        <span class="detail-item-value riwayat-cell-text" id="drawerKategoriBahan">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Barcode Lot :</span>
                        <span class="detail-item-value riwayat-value-bold" id="drawerBarcodeLot">-</span>
                    </div>
                </div>
                <div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Sisa Stok Lot :</span>
                        <span class="detail-item-value riwayat-value-bold" id="drawerSisaStok">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Kedaluwarsa :</span>
                        <span class="detail-item-value" id="drawerExpiryDate">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Pemasok :</span>
                        <span class="detail-item-value" id="drawerSupplier">-</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Dokumen Transaksi Asal --}}
        <div class="detail-card">
            <div class="riwayat-drawer-card-header">
                <h3 class="detail-card-title mb-0">Dokumen Transaksi Asal</h3>
                <span class="riwayat-doc-badge" id="drawerDocType">-</span>
            </div>
            
            <div class="riwayat-doc-box">
                <div>
                    <div class="riwayat-doc-subtitle">Nomor Referensi</div>
                    <div class="riwayat-doc-code" id="drawerDocCode">-</div>
                </div>
                <div>
                    <a href="#" id="drawerDocLink" target="_blank" class="riwayat-doc-link">
                        <span>Buka Dokumen</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Drawer Actions --}}
    <div class="purchase-drawer-footer">
        <button type="button" class="btn-edit-action js-btn-close-drawer">
            Tutup
        </button>
    </div>
</aside>
@endsection

