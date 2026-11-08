@extends('layouts.staff')

@section('title', 'Batch Produksi - Staff SPPG')

@section('content')
<div class="space-y-6 pb-12">
    {{-- Header Section --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div class="page-title-section" style="margin-bottom: 0;">
            <h1 class="page-title">Batch Produksi</h1>
            <p class="page-subtitle">Kelola alur batch produksi menu SPPG dari resep, alokasi lot bahan, hingga hasil porsi.</p>
        </div>
        <button type="button" onclick="openCreateBatchModal()" class="btn-primary" style="padding: 10px 18px; font-size: 13.5px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Buat Batch Baru</span>
        </button>
    </div>

    {{-- 4 Stat Cards Grid --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Total Batch --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Batch</span>
                <span class="stat-value">{{ number_format($stats['total_batch'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Semua batch terdaftar</span>
            </div>
        </div>

        {{-- Card 2: Draft --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Batch Draft</span>
                <span class="stat-value">{{ number_format($stats['draft'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Belum memotong stok</span>
            </div>
        </div>

        {{-- Card 3: Sedang Berjalan (Confirmed & Processing) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 2l4 4-10 10-4-4L18 2z"></path>
                    <path d="M14.5 5.5l4 4"></path>
                    <path d="M3 21l3-3"></path>
                    <path d="M6 18l-3 3"></path>
                    <line x1="8" y1="16" x2="4" y2="20"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Proses Produksi</span>
                <span class="stat-value">{{ number_format($stats['berjalan'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Stok terkunci & diolah</span>
            </div>
        </div>

        {{-- Card 4: Selesai (Ready, Distributed, Completed) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Batch Selesai</span>
                <span class="stat-value">{{ number_format($stats['selesai'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Porsi siap & terdistribusi</span>
            </div>
        </div>
    </div>

    {{-- Filter Bar & Table --}}
    <div class="table-card">
        {{-- Filter Bar Seline --}}
        <form method="GET" action="{{ route('staff.batches.index') }}" id="filterForm" style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; margin-bottom: 16px; overflow-x: auto; padding-bottom: 4px;">
            {{-- Search Box Compact --}}
            <div style="position: relative; width: 170px; flex-shrink: 0;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari batch..." class="form-input" style="padding-left: 32px; height: 38px; font-size: 12.5px; border-radius: 8px; border: 1px solid #CBD5E1; width: 100%;">
            </div>

            {{-- Status Filter --}}
            <div style="width: 140px; flex-shrink: 0;">
                <select name="status" onchange="this.form.submit()" class="form-select" style="height: 38px; font-size: 12.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; width: 100%;">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Terkonfirmasi</option>
                    <option value="processing" {{ $status === 'processing' ? 'selected' : '' }}>Pengolahan</option>
                    <option value="ready" {{ $status === 'ready' ? 'selected' : '' }}>Siap Distribusi</option>
                    <option value="distributed" {{ $status === 'distributed' ? 'selected' : '' }}>Didistribusikan</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            {{-- Menu Filter --}}
            <div style="width: 160px; flex-shrink: 0;">
                <select name="menu_id" onchange="this.form.submit()" class="form-select" style="height: 38px; font-size: 12.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; width: 100%;">
                    <option value="">Semua Menu</option>
                    @foreach ($menus as $m)
                        <option value="{{ $m->id }}" {{ $menuId == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date From & To Seline --}}
            <div style="display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="this.form.submit()" title="Dari Tanggal" class="form-input" style="height: 38px; font-size: 12px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 8px; width: 125px;">
                <span style="color: #94A3B8; font-size: 11.5px;">s/d</span>
                <input type="date" name="date_to" value="{{ $dateTo }}" onchange="this.form.submit()" title="Sampai Tanggal" class="form-input" style="height: 38px; font-size: 12px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 8px; width: 125px;">
            </div>

            @if($q || $status || $menuId || $dateFrom || $dateTo)
                <a href="{{ route('staff.batches.index') }}" class="btn-secondary" style="height: 38px; padding: 0 12px; font-size: 12px; display: inline-flex; align-items: center; flex-shrink: 0;">
                    Reset
                </a>
            @endif
        </form>

        {{-- Table Content (Kolom Pembuat & Kategori Dihapus, Tabel Fit Sesuai Layar Tanpa Scroll Kanan) --}}
        <div class="table-responsive" style="margin-top: 14px;">
            <table class="staff-table data-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 16%; min-width: 135px;">Kode Batch</th>
                        <th style="width: 11%; min-width: 95px;">Tgl Produksi</th>
                        <th style="width: 25%; min-width: 160px;">Menu</th>
                        <th style="width: 8%; min-width: 70px;">Ukuran</th>
                        <th style="width: 7%; text-align: center; min-width: 65px;">Target</th>
                        <th style="width: 7%; text-align: center; min-width: 65px;">Hasil</th>
                        <th style="width: 13%; min-width: 110px;">Status</th>
                        <th style="width: 13%; text-align: right; min-width: 105px;">Biaya / Porsi</th>
                        <th style="width: 95px; text-align: center; white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td>
                                <a href="{{ route('staff.batches.show', $batch->id) }}" style="font-weight: 700; color: #042A70; text-decoration: none;">
                                    {{ $batch->batch_code }}
                                </a>
                            </td>
                            <td style="color: #475569; font-size: 13px;">
                                {{ $batch->production_date ? $batch->production_date->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #071E49;">{{ $batch->menu?->nama ?? '-' }}</div>
                            </td>
                            <td>
                                @php $ukuran = $batch->recipe?->ukuran_porsi; @endphp
                                <span class="chip-porsi {{ $ukuran === 'besar' ? 'chip-porsi-besar' : 'chip-porsi-kecil' }}">
                                    {{ ucfirst($ukuran ?? '-') }}
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 600; color: #071E49;">
                                {{ number_format($batch->target_porsi, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if($batch->actual_porsi !== null)
                                    <span style="font-weight: 700; color: #16A34A;">
                                        {{ number_format($batch->actual_porsi, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span style="color: #94A3B8;">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status {{ $batch->status_badge_class }}">
                                    <span class="status-dot"></span>
                                    <span>{{ $batch->status_label }}</span>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                @if($batch->biaya_per_porsi !== null)
                                    <span style="font-weight: 700; color: #071E49;">
                                        Rp {{ number_format($batch->biaya_per_porsi, 0, ',', '.') }}
                                    </span>
                                @elseif($batch->estimasi_biaya_per_porsi !== null)
                                    <span style="font-size: 11.5px; color: #64748B;" title="Estimasi berdasarkan target porsi">
                                        ~Rp {{ number_format($batch->estimasi_biaya_per_porsi, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span style="color: #94A3B8;">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    {{-- Detail --}}
                                    <a href="{{ route('staff.batches.show', $batch->id) }}" class="btn-action-icon" title="Lihat Detail Batch">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    @if ($batch->status === 'draft')
                                        {{-- Ubah (hanya draft - buka modal form pop up) --}}
                                        <button type="button" class="btn-action-icon" title="Ubah Batch Draft" onclick="openEditBatchModal({{ $batch->id }})">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>

                                        {{-- Konfirmasi (hanya draft) --}}
                                        <button type="button" class="btn-action-icon" title="Konfirmasi Batch (Potong Stok)" onclick="openConfirmModal({{ $batch->id }}, '{{ $batch->batch_code }}', '{{ $batch->menu?->nama }}', {{ $batch->target_porsi }})" style="color: #16A34A; border-color: #BBF7D0; background: #F0FDF4;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </button>

                                        {{-- Batalkan (hanya draft) --}}
                                        <button type="button" class="btn-action-icon danger" title="Batalkan Batch" onclick="openCancelModal({{ $batch->id }}, '{{ $batch->batch_code }}')">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 30px 16px; color: #64748B;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #F1F5F9; display: flex; align-items: center; justify-content: center; color: #94A3B8;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                                            <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                                            <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                                            <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                        </svg>
                                    </div>
                                    <div style="font-weight: 600; font-size: 13.5px; color: #071E49;">Belum Ada Data Batch Produksi</div>
                                    <p style="font-size: 12px; color: #64748B; margin: 0; max-width: 380px; line-height: 1.45;">
                                        Silakan buat batch produksi baru dengan memilih menu aktif dan mengalokasikan lot bahan baku.
                                    </p>
                                    <button type="button" onclick="openCreateBatchModal()" class="btn-primary" style="margin-top: 4px; font-size: 12px; padding: 6px 14px;">
                                        Buat Batch Baru
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($batches->hasPages())
            <div style="margin-top: 20px;">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL BUAT BATCH POP UP DI TENGAH (CUKUP NOMOR 1 & 2 TANPA TULISAN LANGKAH) --}}
<div id="modalCreateBatch" class="modal-backdrop-custom">
    <div class="modal-content-custom modal-large">
        <div class="modal-header-custom">
            <div>
                <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #071E49;">Buat Batch Produksi</h3>
                <p style="margin: 2px 0 0; font-size: 12px; color: #64748B;">Satu batch = satu menu = satu ukuran porsi. Alokasikan lot bahan baku dengan aturan FEFO.</p>
            </div>
            <button type="button" onclick="closeCreateBatchModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form id="createBatchForm" method="POST" action="{{ route('staff.batches.store') }}">
            @csrf
            <input type="hidden" name="action" id="modalFormAction" value="draft">

            <div class="modal-body-custom" style="padding: 20px 24px;">
                {{-- Bagian 1: Cukup Nomor 1 (Tanpa tulisan 'Langkah 1') --}}
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 26px; height: 26px; border-radius: 50%; background: #042A70; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 700;">
                            1
                        </div>
                        <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: #071E49;">Menu & Target Porsi</h4>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                        {{-- Kode Batch --}}
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Kode Batch (Otomatis)</label>
                            <input type="text" value="{{ $autoBatchCode }}" readonly class="form-input" style="height: 38px; font-weight: 700; color: #042A70; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; width: 100%; padding: 0 10px; font-size: 12.5px;">
                        </div>

                        {{-- Tanggal Produksi --}}
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Tanggal Produksi <span style="color: #DC2626;">*</span></label>
                            <input type="date" name="production_date" id="modalProductionDate" value="{{ $today }}" required class="form-input" style="height: 38px; font-size: 12.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; width: 100%;" onchange="onModalStepOneChange()">
                        </div>

                        {{-- Menu Pilihan --}}
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Menu Aktif <span style="color: #DC2626;">*</span></label>
                            <select name="menu_id" id="modalMenuSelect" required class="form-select" style="height: 38px; font-size: 12.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; width: 100%;" onchange="onModalMenuSelected()">
                                <option value="">-- Pilih Menu --</option>
                                @foreach ($menusWithRecipes as $m)
                                    <option value="{{ $m->id }}" data-kategori="{{ $m->kategori_label }}" data-recipes="{{ json_encode($m->recipes) }}">
                                        {{ $m->nama }} ({{ $m->kategori_label }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Ukuran Porsi --}}
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Ukuran Porsi <span style="color: #DC2626;">*</span></label>
                            <div style="display: flex; gap: 8px;">
                                <label id="modalLabelPorsiBesar" style="flex: 1; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; height: 38px;">
                                    <span style="font-size: 12.5px; font-weight: 600; color: #071E49;">Besar</span>
                                    <input type="radio" name="ukuran_porsi" value="besar" id="modalRadioPorsiBesar" onchange="onModalStepOneChange()" style="accent-color: #042A70;">
                                </label>
                                <label id="modalLabelPorsiKecil" style="flex: 1; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; height: 38px;">
                                    <span style="font-size: 12.5px; font-weight: 600; color: #071E49;">Kecil</span>
                                    <input type="radio" name="ukuran_porsi" value="kecil" id="modalRadioPorsiKecil" onchange="onModalStepOneChange()" style="accent-color: #042A70;">
                                </label>
                            </div>
                        </div>

                        {{-- Target Porsi --}}
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Target Porsi <span style="color: #DC2626;">*</span></label>
                            <div style="position: relative;">
                                <input type="number" name="target_porsi" id="modalTargetPorsi" value="100" min="1" step="1" required class="form-input" style="height: 38px; font-size: 13.5px; font-weight: 700; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 50px 0 10px; width: 100%; color: #071E49;" oninput="onModalStepOneChange()">
                                <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; font-weight: 600; color: #64748B;">porsi</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian 2: Cukup Nomor 2 (Tanpa tulisan 'Langkah 2') --}}
                <div id="modalStepTwoSection" style="display: none;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 26px; height: 26px; border-radius: 50%; background: #042A70; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 700;">
                                2
                            </div>
                            <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: #071E49;">Kebutuhan & Alokasi Lot Bahan</h4>
                        </div>
                        <button type="button" onclick="openExtraMaterialModal()" class="btn-secondary" style="font-size: 12px; padding: 6px 12px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Tambah Bahan di Luar Resep</span>
                        </button>
                    </div>

                    {{-- Loading State --}}
                    <div id="modalLoadingBlocks" style="display: none; text-align: center; padding: 30px; background: #F8FAFC; border-radius: 10px; border: 1px solid #E2E8F0;">
                        <div style="display: inline-block; width: 28px; height: 28px; border: 3px solid #E2E8F0; border-top-color: #042A70; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        <div style="font-size: 12.5px; color: #64748B; margin-top: 8px;">Membaca stok lot FEFO...</div>
                    </div>

                    <div id="modalMaterialBlocksContainer"></div>
                    <div id="modalExtraMaterialsContainer"></div>
                </div>
            </div>

            <div class="modal-footer-custom" style="padding: 14px 24px; background: #FAFAFA;">
                <div style="flex: 1; text-align: left;">
                    <span id="modalOverallShortageText" style="font-size: 12.5px; font-weight: 600; color: #64748B;">Lengkapi menu & target porsi</span>
                </div>
                <button type="button" onclick="closeCreateBatchModal()" class="btn-secondary" style="font-size: 12.5px; padding: 7px 16px;">Batal</button>
                <button type="button" onclick="submitModalBatch('draft')" class="btn-primary" style="font-size: 12.5px; font-weight: 600; background: #042A70; border-color: #042A70; padding: 7px 18px;">
                    Simpan Batch (Draft)
                </button>
                <button type="button" onclick="confirmAndSubmitModalBatch()" class="btn-success" style="font-size: 12.5px; padding: 7px 18px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Konfirmasi Batch</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL UBAH BATCH POP UP DI TENGAH (CUKUP NOMOR 1 & 2 TANPA TULISAN LANGKAH) --}}
<div id="modalEditBatch" class="modal-backdrop-custom">
    <div class="modal-content-custom modal-large">
        <div class="modal-header-custom">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #071E49;" id="editModalBatchTitle">Ubah Batch Produksi</h3>
                <p style="margin: 2px 0 0; font-size: 12px; color: #64748B;">Perbarui tanggal produksi, target porsi, atau sesuaikan alokasi lot bahan baku.</p>
            </div>
            <button type="button" onclick="closeEditBatchModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form id="editBatchForm" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="action" id="editModalFormAction" value="draft">

            <div class="modal-body-custom" style="padding: 20px 24px;">
                {{-- Bagian 1: Cukup Nomor 1 --}}
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 26px; height: 26px; border-radius: 50%; background: #042A70; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 700;">
                            1
                        </div>
                        <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: #071E49;">Informasi Batch & Target Porsi</h4>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                        {{-- Kode Batch --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Kode Batch</label>
                            <input type="text" id="editBatchCode" readonly class="form-input" style="height: 38px; font-weight: 700; color: #042A70; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; width: 100%; padding: 0 10px; font-size: 12.5px;">
                        </div>

                        {{-- Tanggal Produksi --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Tanggal Produksi <span style="color: #DC2626;">*</span></label>
                            <input type="date" name="production_date" id="editProductionDate" required class="form-input" style="height: 38px; font-size: 12.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; width: 100%;" onchange="onEditStepOneChange()">
                        </div>

                        {{-- Menu --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Menu & Kategori</label>
                            <input type="text" id="editMenuDisplay" readonly class="form-input" style="height: 38px; font-weight: 600; color: #071E49; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; width: 100%; padding: 0 10px; font-size: 12.5px;">
                        </div>

                        {{-- Ukuran Porsi --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Ukuran Porsi</label>
                            <input type="text" id="editUkuranDisplay" readonly class="form-input" style="height: 38px; font-weight: 600; color: #071E49; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; width: 100%; padding: 0 10px; font-size: 12.5px;">
                        </div>

                        {{-- Target Porsi --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Target Porsi <span style="color: #DC2626;">*</span></label>
                            <div style="position: relative;">
                                <input type="number" name="target_porsi" id="editTargetPorsi" min="1" step="1" required class="form-input" style="height: 38px; font-size: 13.5px; font-weight: 700; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 50px 0 10px; width: 100%; color: #071E49;" oninput="onEditStepOneChange()">
                                <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; font-weight: 600; color: #64748B;">porsi</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian 2: Cukup Nomor 2 --}}
                <div id="editStepTwoSection">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 26px; height: 26px; border-radius: 50%; background: #042A70; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 700;">
                                2
                            </div>
                            <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: #071E49;">Kebutuhan & Alokasi Lot Bahan</h4>
                        </div>
                        <button type="button" onclick="openExtraMaterialModal('edit')" class="btn-secondary" style="font-size: 12px; padding: 6px 12px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Tambah Bahan di Luar Resep</span>
                        </button>
                    </div>

                    {{-- Loading State --}}
                    <div id="editLoadingBlocks" style="display: none; text-align: center; padding: 30px; background: #F8FAFC; border-radius: 10px; border: 1px solid #E2E8F0;">
                        <div style="display: inline-block; width: 28px; height: 28px; border: 3px solid #E2E8F0; border-top-color: #042A70; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        <div style="font-size: 12.5px; color: #64748B; margin-top: 8px;">Memuat stok lot bahan...</div>
                    </div>

                    <div id="editMaterialBlocksContainer"></div>
                    <div id="editExtraMaterialsContainer"></div>
                </div>
            </div>

            <div class="modal-footer-custom" style="padding: 14px 24px; background: #FAFAFA;">
                <div style="flex: 1; text-align: left;">
                    <span id="editOverallShortageText" style="font-size: 12px; font-weight: 600; color: #64748B;">Memuat data alokasi...</span>
                </div>
                <button type="button" onclick="closeEditBatchModal()" class="btn-secondary" style="font-size: 12.5px; padding: 7px 16px;">Batal</button>
                <button type="button" onclick="submitEditModalBatch('draft')" class="btn-primary" style="font-size: 12.5px; font-weight: 600; background: #042A70; border-color: #042A70; padding: 7px 18px;">
                    Simpan Perubahan (Draft)
                </button>
                <button type="button" onclick="confirmAndSubmitEditModalBatch()" class="btn-success" style="font-size: 12.5px; padding: 7px 18px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Konfirmasi Batch</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL CUSTOM ALERT & CONFIRM DI TENGAH (GAYA MODERN, BUKAN DEFAULT BROWSER) --}}
<div id="modalCustomAlert" class="modal-backdrop-custom" style="z-index: 10005;">
    <div class="modal-content-custom" style="max-width: 440px; text-align: center; border-radius: 14px; overflow: hidden;">
        <div style="padding: 24px 20px;">
            <div id="customAlertIconBox" style="width: 48px; height: 48px; border-radius: 50%; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; background: #FEF3C7; color: #D97706;">
                <svg id="customAlertIconWarning" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <svg id="customAlertIconConfirm" style="display: none;" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <svg id="customAlertIconSuccess" style="display: none;" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>

            <h3 id="customAlertTitle" style="margin: 0 0 6px; font-size: 16px; font-weight: 700; color: #071E49;">Peringatan</h3>
            <p id="customAlertMessage" style="margin: 0; font-size: 13px; color: #475569; line-height: 1.5;"></p>
            
            <div id="customAlertDetails" style="display: none; margin-top: 12px; text-align: left; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #92400E; max-height: 140px; overflow-y: auto;">
            </div>
        </div>

        <div id="customAlertFooter" style="padding: 12px 20px; background: #FAFAFA; border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: center; gap: 10px;">
            <button type="button" id="customAlertCancelBtn" style="display: none;" onclick="closeCustomAlert()" class="btn-secondary" style="font-size: 12.5px; padding: 7px 16px;">
                Batal
            </button>
            <button type="button" id="customAlertOkBtn" onclick="onCustomAlertOk()" class="btn-primary" style="font-size: 12.5px; padding: 7px 20px;">
                Mengerti
            </button>
        </div>
    </div>
</div>

{{-- Modal Tambah Bahan di Luar Resep --}}
<div id="modalExtraMaterial" class="modal-backdrop-custom" style="z-index: 10001;">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #071E49;">Tambah Bahan di Luar Resep</h3>
            <button type="button" onclick="closeExtraMaterialModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="modal-body-custom">
            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Pilih Bahan Tambahan</label>
            <select id="extraMaterialSelect" class="form-select" style="height: 38px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; width: 100%; padding: 0 10px;">
                <option value="">-- Pilih Bahan --</option>
                @foreach ($allMaterials as $mat)
                    <option value="{{ $mat->id }}">{{ $mat->nama }} ({{ $mat->satuan ?: 'kg' }})</option>
                @endforeach
            </select>
        </div>
        <div class="modal-footer-custom">
            <button type="button" onclick="closeExtraMaterialModal()" class="btn-secondary" style="font-size: 12.5px;">Batal</button>
            <button type="button" onclick="confirmAddExtraMaterial()" class="btn-primary" style="font-size: 12.5px;">Tambahkan</button>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Batch --}}
<div id="modalConfirmBatch" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #071E49;">Konfirmasi Batch Produksi</h3>
                    <p style="margin: 2px 0 0; font-size: 12px; color: #64748B;" id="confirmBatchSubtitle">Kode Batch</p>
                </div>
            </div>
            <button type="button" onclick="closeConfirmModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form id="formConfirmBatch" method="POST" action="">
            @csrf
            <div class="modal-body-custom">
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 14px; font-size: 12.5px; color: #334155;">
                    Dengan mengonfirmasi batch ini:
                    <ul style="margin: 6px 0 0 16px; padding: 0; color: #475569;">
                        <li>Stok fisik bahan pada masing-masing lot terpilih akan <strong>langsung dipotong</strong> (mutasi <em>PRODUCTION_OUT</em>).</li>
                        <li>Harga satuan lot saat ini akan dicatat permanen (<em>snapshot unit cost</em>).</li>
                        <li>Dokumen batch akan <strong>terkunci</strong> (<em>is_locked = true</em>) dan siap masuk ke tahap pengolahan.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" onclick="closeConfirmModal()" class="btn-secondary" style="font-size: 12.5px;">Batal</button>
                <button type="submit" class="btn-success" style="font-size: 12.5px;">Konfirmasi & Potong Stok</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Batalkan Batch --}}
<div id="modalCancelBatch" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #071E49;">Batalkan Batch Produksi</h3>
            <button type="button" onclick="closeCancelModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form id="formCancelBatch" method="POST" action="">
            @csrf
            <div class="modal-body-custom">
                <p style="font-size: 13px; color: #475569; margin: 0 0 12px;" id="cancelBatchSubtitle">
                    Apakah Anda yakin ingin membatalkan draft batch produksi ini?
                </p>
                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Alasan Pembatalan <span style="color: #DC2626;">*</span>
                    </label>
                    <textarea name="reason" required rows="3" class="form-input" placeholder="Contoh: Perubahan menu sekolah, kesalahan input..." style="width: 100%; font-size: 12.5px; padding: 8px 10px; border-radius: 8px; border: 1px solid #CBD5E1;"></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" onclick="closeCancelModal()" class="btn-secondary" style="font-size: 12.5px;">Kembali</button>
                <button type="submit" class="btn-danger-outline" style="background: #DC2626; color: #FFFFFF; border: none; font-size: 12.5px;">Ya, Batalkan Batch</button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

@push('scripts')
<script>
    let currentBlocks = [];
    let editCurrentBlocks = [];
    let editBatchData = null;
    let extraMaterialTarget = 'create';
    let customAlertCallback = null;

    /* =========================================================================
       CUSTOM STYLED ALERT & CONFIRM DI TENGAH (BUKAN DEFAULT BROWSER)
       ========================================================================= */
    function showStyledAlert(title, message, details = null, type = 'warning') {
        const modal = document.getElementById('modalCustomAlert');
        const iconBox = document.getElementById('customAlertIconBox');
        const iconWarning = document.getElementById('customAlertIconWarning');
        const iconConfirm = document.getElementById('customAlertIconConfirm');
        const iconSuccess = document.getElementById('customAlertIconSuccess');
        const titleEl = document.getElementById('customAlertTitle');
        const msgEl = document.getElementById('customAlertMessage');
        const detailsEl = document.getElementById('customAlertDetails');
        const cancelBtn = document.getElementById('customAlertCancelBtn');
        const okBtn = document.getElementById('customAlertOkBtn');

        titleEl.textContent = title;
        msgEl.textContent = message;

        if (details && Array.isArray(details) && details.length > 0) {
            detailsEl.style.display = 'block';
            detailsEl.innerHTML = `<ul style="margin: 0; padding-left: 18px; line-height: 1.6;">${details.map(d => `<li>${d}</li>`).join('')}</ul>`;
        } else if (details && typeof details === 'string') {
            detailsEl.style.display = 'block';
            detailsEl.textContent = details;
        } else {
            detailsEl.style.display = 'none';
            detailsEl.innerHTML = '';
        }

        cancelBtn.style.display = 'none';
        okBtn.textContent = 'Mengerti';
        okBtn.className = 'btn-primary';
        customAlertCallback = null;

        if (type === 'error') {
            iconBox.style.background = '#FEF2F2';
            iconBox.style.color = '#DC2626';
            iconWarning.style.display = 'block';
            iconConfirm.style.display = 'none';
            iconSuccess.style.display = 'none';
        } else if (type === 'success') {
            iconBox.style.background = '#F0FDF4';
            iconBox.style.color = '#16A34A';
            iconWarning.style.display = 'none';
            iconConfirm.style.display = 'none';
            iconSuccess.style.display = 'block';
        } else {
            iconBox.style.background = '#FEF3C7';
            iconBox.style.color = '#D97706';
            iconWarning.style.display = 'block';
            iconConfirm.style.display = 'none';
            iconSuccess.style.display = 'none';
        }

        modal.classList.add('show');
    }

    function showStyledConfirm(title, message, onConfirm, confirmBtnText = 'Ya, Lanjutkan') {
        const modal = document.getElementById('modalCustomAlert');
        const iconBox = document.getElementById('customAlertIconBox');
        const iconWarning = document.getElementById('customAlertIconWarning');
        const iconConfirm = document.getElementById('customAlertIconConfirm');
        const iconSuccess = document.getElementById('customAlertIconSuccess');
        const titleEl = document.getElementById('customAlertTitle');
        const msgEl = document.getElementById('customAlertMessage');
        const detailsEl = document.getElementById('customAlertDetails');
        const cancelBtn = document.getElementById('customAlertCancelBtn');
        const okBtn = document.getElementById('customAlertOkBtn');

        titleEl.textContent = title;
        msgEl.textContent = message;
        detailsEl.style.display = 'none';

        iconBox.style.background = '#EFF6FF';
        iconBox.style.color = '#0284C7';
        iconWarning.style.display = 'none';
        iconConfirm.style.display = 'block';
        iconSuccess.style.display = 'none';

        cancelBtn.style.display = 'inline-flex';
        okBtn.textContent = confirmBtnText;
        okBtn.className = 'btn-success';

        customAlertCallback = onConfirm;
        modal.classList.add('show');
    }

    function onCustomAlertOk() {
        const cb = customAlertCallback;
        closeCustomAlert();
        if (typeof cb === 'function') {
            cb();
        }
    }

    function closeCustomAlert() {
        document.getElementById('modalCustomAlert').classList.remove('show');
        customAlertCallback = null;
    }

    /* =========================================================================
       MODAL BUAT BATCH BARU
       ========================================================================= */
    function openCreateBatchModal() {
        document.getElementById('modalCreateBatch').classList.add('show');
    }

    function closeCreateBatchModal() {
        document.getElementById('modalCreateBatch').classList.remove('show');
    }

    function onModalMenuSelected() {
        const menuSelect = document.getElementById('modalMenuSelect');
        const selectedOption = menuSelect.options[menuSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            document.getElementById('modalRadioPorsiBesar').disabled = true;
            document.getElementById('modalRadioPorsiKecil').disabled = true;
            document.getElementById('modalStepTwoSection').style.display = 'none';
            return;
        }

        const recipesData = JSON.parse(selectedOption.getAttribute('data-recipes') || '[]');
        const availableUkuran = recipesData.map(r => r.ukuran_porsi);

        const radioBesar = document.getElementById('modalRadioPorsiBesar');
        const radioKecil = document.getElementById('modalRadioPorsiKecil');
        const labelBesar = document.getElementById('modalLabelPorsiBesar');
        const labelKecil = document.getElementById('modalLabelPorsiKecil');

        radioBesar.disabled = !availableUkuran.includes('besar');
        radioKecil.disabled = !availableUkuran.includes('kecil');

        labelBesar.style.opacity = radioBesar.disabled ? '0.4' : '1';
        labelKecil.style.opacity = radioKecil.disabled ? '0.4' : '1';

        if (!radioBesar.checked && !radioKecil.checked) {
            if (availableUkuran.includes('besar')) {
                radioBesar.checked = true;
            } else if (availableUkuran.includes('kecil')) {
                radioKecil.checked = true;
            }
        } else if (radioBesar.checked && radioBesar.disabled) {
            radioBesar.checked = false;
            if (!radioKecil.disabled) radioKecil.checked = true;
        } else if (radioKecil.checked && radioKecil.disabled) {
            radioKecil.checked = false;
            if (!radioBesar.disabled) radioBesar.checked = true;
        }

        onModalStepOneChange();
    }

    function onModalStepOneChange() {
        const menuId = document.getElementById('modalMenuSelect').value;
        const ukuran = document.querySelector('input[name="ukuran_porsi"]:checked')?.value;
        const target = parseInt(document.getElementById('modalTargetPorsi').value || '0', 10);
        const date = document.getElementById('modalProductionDate').value;

        if (!menuId || !ukuran || target <= 0 || !date) {
            document.getElementById('modalStepTwoSection').style.display = 'none';
            return;
        }

        fetchModalRecipeMaterials(menuId, ukuran, target, date);
    }

    async function fetchModalRecipeMaterials(menuId, ukuran, target, date) {
        const stepTwo = document.getElementById('modalStepTwoSection');
        const loader = document.getElementById('modalLoadingBlocks');
        const container = document.getElementById('modalMaterialBlocksContainer');

        stepTwo.style.display = 'block';
        loader.style.display = 'block';
        container.innerHTML = '';

        try {
            const res = await fetch(`/staff/batch/recipe-materials?menu_id=${menuId}&ukuran_porsi=${ukuran}&target_porsi=${target}&production_date=${date}`);
            const data = await res.json();
            loader.style.display = 'none';

            if (!data.success) {
                container.innerHTML = `<div class="alert alert-error" style="background:#FEE2E2; color:#B91C1C; padding:12px; border-radius:8px; font-size:12.5px;">${data.message}</div>`;
                return;
            }

            currentBlocks = data.blocks || [];
            renderModalMaterialBlocks();
        } catch (e) {
            loader.style.display = 'none';
            container.innerHTML = `<div class="alert alert-error">Terjadi kesalahan: ${e.message}</div>`;
        }
    }

    function renderModalMaterialBlocks() {
        const container = document.getElementById('modalMaterialBlocksContainer');
        container.innerHTML = '';

        currentBlocks.forEach((block) => {
            const card = document.createElement('div');
            card.className = 'material-block-card';
            card.style.padding = '14px 16px';
            card.style.marginBottom = '14px';

            let lotsHtml = '';
            if (!block.lots || block.lots.length === 0) {
                lotsHtml = `
                    <div style="text-align: center; padding: 16px; color: #DC2626; font-size: 12px; background: #FEF2F2; border-radius: 8px;">
                        Tidak ada lot tersedia untuk bahan ini sebelum tanggal produksi.
                    </div>
                `;
            } else {
                lotsHtml = `
                    <div class="table-responsive" style="margin-top: 8px;">
                        <table class="staff-table data-table" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th style="width: 130px; padding: 8px 10px;">Barcode</th>
                                    <th style="width: 120px; padding: 8px 10px;">No. Lot</th>
                                    <th style="width: 120px; padding: 8px 10px;">Kedaluwarsa</th>
                                    <th style="width: 100px; padding: 8px 10px;">Sisa Stok</th>
                                    <th style="min-width: 130px; padding: 8px 10px;">Pemasok</th>
                                    <th style="width: 140px; padding: 8px 10px;">Pakai (${block.satuan})</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${block.lots.map((lot, lIdx) => {
                                    const isExpired = lot.days_to_expiry !== null && lot.days_to_expiry < 0;
                                    const isNear = lot.is_near_expiry;
                                    const expiryBadge = isExpired
                                        ? `<span class="badge-status badge-expired"><span class="status-dot"></span>Kedaluwarsa</span>`
                                        : (isNear ? `<span class="badge-status badge-near-expired"><span class="status-dot"></span>H-${lot.days_to_expiry}</span>` : `<span style="color:#16A34A; font-weight:600;">${lot.expiry_date_formatted}</span>`);

                                    return `
                                        <tr class="${isExpired ? 'opacity-50' : ''}">
                                            <td style="font-family: monospace; font-weight: 600; padding: 8px 10px;">${lot.barcode}</td>
                                            <td style="padding: 8px 10px;">${lot.lot_number || '-'}</td>
                                            <td style="padding: 8px 10px;">${expiryBadge}</td>
                                            <td style="font-weight: 600; padding: 8px 10px;">${lot.current_qty} ${block.satuan}</td>
                                            <td style="color: #64748B; padding: 8px 10px;">${lot.supplier_name}</td>
                                            <td style="padding: 8px 10px;">
                                                <input type="number" 
                                                       name="usages[${lot.id}][qty_used]"
                                                       data-material-id="${block.material_id}"
                                                       data-lot-id="${lot.id}"
                                                       data-max-qty="${lot.current_qty}"
                                                       data-lot-idx="${lIdx}"
                                                       class="form-input qty-usage-input"
                                                       min="0"
                                                       max="${lot.current_qty}"
                                                       step="0.001"
                                                       placeholder="0.000"
                                                       ${isExpired ? 'disabled' : ''}
                                                       oninput="onModalQtyInput(this, ${block.material_id}, ${lot.current_qty})"
                                                       style="width: 100%; height: 32px; font-size: 12.5px; font-weight: 600; padding: 0 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                                                <input type="hidden" name="usages[${lot.id}][material_lot_id]" value="${lot.id}">
                                                <div id="mwarn_${lot.id}" style="display:none; font-size: 11px; color: #DC2626; margin-top: 2px;"></div>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            card.innerHTML = `
                <div class="material-block-header" style="padding-bottom: 10px; margin-bottom: 10px;">
                    <div class="material-title-group">
                        <div class="material-avatar" style="width: 32px; height: 32px; font-size: 13px;">${block.material_name.charAt(0).toUpperCase()}</div>
                        <div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #071E49;">${block.material_name}</h4>
                            <div style="font-size: 11.5px; color: #64748B;">Resep: ${block.qty_per_porsi} ${block.satuan} / porsi</div>
                        </div>
                    </div>
                    <div class="material-pills-group">
                        <span class="pill-info" style="font-size: 11.5px; padding: 3px 8px;">Kebutuhan: <strong>${block.kebutuhan} ${block.satuan}</strong></span>
                        <span class="pill-info" id="mselected_${block.material_id}" style="font-size: 11.5px; padding: 3px 8px;">Terpilih: <strong>0.000 ${block.satuan}</strong></span>
                        <span class="pill-kurang" id="mstatusPill_${block.material_id}" style="font-size: 11.5px; padding: 3px 10px;">Kurang ${block.kebutuhan} ${block.satuan}</span>
                        ${block.lots && block.lots.length > 0 ? `
                            <button type="button" class="btn-fefo" onclick="autoFillModalFefo(${block.material_id})" style="font-size: 11.5px; padding: 4px 10px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                <span>Isi otomatis FEFO</span>
                            </button>
                        ` : ''}
                    </div>
                </div>
                ${lotsHtml}
            `;

            container.appendChild(card);
        });

        updateModalOverallSummary();
    }

    function onModalQtyInput(input, materialId, maxQty) {
        const val = parseFloat(input.value) || 0;
        const warnDiv = document.getElementById(`mwarn_${input.getAttribute('data-lot-id')}`);

        if (val > maxQty) {
            warnDiv.style.display = 'block';
            warnDiv.textContent = `Stok lot hanya ${maxQty}`;
            input.style.borderColor = '#DC2626';
        } else {
            warnDiv.style.display = 'none';
            input.style.borderColor = '#CBD5E1';
        }

        updateModalMaterialBlockStatus(materialId);
        updateModalOverallSummary();
    }

    function updateModalMaterialBlockStatus(materialId) {
        const block = currentBlocks.find(b => b.material_id === materialId);
        if (!block) return;

        const inputs = document.querySelectorAll(`#modalMaterialBlocksContainer .qty-usage-input[data-material-id="${materialId}"]`);
        let totalUsed = 0;
        inputs.forEach(inp => totalUsed += (parseFloat(inp.value) || 0));

        totalUsed = Math.round(totalUsed * 1000) / 1000;
        const pillSelected = document.getElementById(`mselected_${materialId}`);
        const pillStatus = document.getElementById(`mstatusPill_${materialId}`);

        if (pillSelected) pillSelected.innerHTML = `Terpilih: <strong>${totalUsed.toFixed(3)} ${block.satuan}</strong>`;

        const diff = Math.round((block.kebutuhan - totalUsed) * 1000) / 1000;
        if (pillStatus) {
            if (diff <= 0) {
                pillStatus.className = 'pill-terpenuhi';
                pillStatus.innerHTML = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Terpenuhi</span>`;
            } else {
                pillStatus.className = 'pill-kurang';
                pillStatus.innerHTML = `Kurang ${diff.toFixed(3)} ${block.satuan}`;
            }
        }
    }

    function autoFillModalFefo(materialId) {
        const block = currentBlocks.find(b => b.material_id === materialId);
        if (!block) return;

        let needed = block.kebutuhan;
        const inputs = Array.from(document.querySelectorAll(`#modalMaterialBlocksContainer .qty-usage-input[data-material-id="${materialId}"]`));

        inputs.forEach(input => {
            if (input.disabled) return;
            const max = parseFloat(input.getAttribute('data-max-qty') || '0');

            if (needed <= 0) {
                input.value = '';
            } else if (max <= needed) {
                input.value = max.toFixed(3);
                needed = Math.round((needed - max) * 1000) / 1000;
            } else {
                input.value = needed.toFixed(3);
                needed = 0;
            }
            input.dispatchEvent(new Event('input'));
        });
    }

    function updateModalOverallSummary() {
        let allSatisfied = true;
        let shortages = [];

        currentBlocks.forEach(b => {
            const inputs = document.querySelectorAll(`#modalMaterialBlocksContainer .qty-usage-input[data-material-id="${b.material_id}"]`);
            let total = 0;
            inputs.forEach(inp => total += (parseFloat(inp.value) || 0));

            const diff = Math.round((b.kebutuhan - total) * 1000) / 1000;
            if (diff > 0) {
                allSatisfied = false;
                shortages.push(`${b.material_name} kurang ${diff} ${b.satuan}`);
            }
        });

        const summaryText = document.getElementById('modalOverallShortageText');
        if (summaryText) {
            if (allSatisfied && currentBlocks.length > 0) {
                summaryText.style.color = '#16A34A';
                summaryText.textContent = 'Semua bahan resep telah terpenuhi!';
            } else if (shortages.length > 0) {
                summaryText.style.color = '#DC2626';
                summaryText.textContent = shortages.join(' • ');
            }
        }
    }

    function submitModalBatch(actionType) {
        document.getElementById('modalFormAction').value = actionType;

        // Disable lot inputs yang kosong/0 agar tidak memicu validasi error di backend
        document.querySelectorAll('#modalCreateBatch .qty-usage-input').forEach(input => {
            const val = parseFloat(input.value) || 0;
            if (val <= 0) {
                input.disabled = true;
                const hiddenInput = input.parentElement.querySelector('input[type="hidden"]');
                if (hiddenInput) hiddenInput.disabled = true;
            }
        });

        document.getElementById('createBatchForm').submit();
    }

    function confirmAndSubmitModalBatch() {
        let unfulfilled = [];
        currentBlocks.forEach(b => {
            const inputs = document.querySelectorAll(`#modalMaterialBlocksContainer .qty-usage-input[data-material-id="${b.material_id}"]`);
            let total = 0;
            inputs.forEach(inp => total += (parseFloat(inp.value) || 0));

            const diff = Math.round((b.kebutuhan - total) * 1000) / 1000;
            if (diff > 0) {
                unfulfilled.push(`Bahan ${b.material_name} masih kurang ${diff} ${b.satuan}`);
            }
        });

        if (unfulfilled.length > 0) {
            showStyledAlert("Konfirmasi Ditolak", "Semua bahan resep harus memenuhi target sebelum batch dapat dikonfirmasi.", unfulfilled, "warning");
            return;
        }

        showStyledConfirm(
            "Konfirmasi Batch Sekarang?",
            "Stok bahan akan langsung dipotong dari lot gudang dan batch akan dikunci ke status terkonfirmasi.",
            () => submitModalBatch('confirm'),
            "Ya, Konfirmasi Batch"
        );
    }

    /* =========================================================================
       MODAL UBAH BATCH DRAFT (FORM POP UP DI TENGAH SAMA SEPERTI BUAT BATCH)
       ========================================================================= */
    async function openEditBatchModal(batchId) {
        const modal = document.getElementById('modalEditBatch');
        const form = document.getElementById('editBatchForm');
        form.action = `/staff/batch/${batchId}`;

        // Reset
        document.getElementById('editBatchCode').value = 'Memuat...';
        document.getElementById('editProductionDate').value = '';
        document.getElementById('editMenuDisplay').value = 'Memuat...';
        document.getElementById('editUkuranDisplay').value = 'Memuat...';
        document.getElementById('editTargetPorsi').value = '';
        document.getElementById('editMaterialBlocksContainer').innerHTML = '';
        document.getElementById('editExtraMaterialsContainer').innerHTML = '';
        document.getElementById('editLoadingBlocks').style.display = 'block';

        modal.classList.add('show');

        try {
            const res = await fetch(`/staff/batch/${batchId}/edit`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (!data.success) {
                showStyledAlert("Gagal Membuka Batch", data.message || "Data batch tidak dapat dimuat.", null, "error");
                closeEditBatchModal();
                return;
            }

            editBatchData = data.batch;
            document.getElementById('editModalBatchTitle').textContent = `Ubah Batch: ${editBatchData.batch_code}`;
            document.getElementById('editBatchCode').value = editBatchData.batch_code;
            document.getElementById('editProductionDate').value = editBatchData.production_date;
            document.getElementById('editMenuDisplay').value = `${editBatchData.menu_nama} (${editBatchData.kategori_label})`;
            document.getElementById('editUkuranDisplay').value = `Porsi ${editBatchData.ukuran_porsi.charAt(0).toUpperCase() + editBatchData.ukuran_porsi.slice(1)}`;
            document.getElementById('editTargetPorsi').value = editBatchData.target_porsi;

            await fetchEditRecipeMaterials(editBatchData.menu_id, editBatchData.ukuran_porsi, editBatchData.target_porsi, editBatchData.production_date, editBatchData.usages || []);
        } catch (e) {
            showStyledAlert("Terjadi Kesalahan", "Gagal memuat batch: " + e.message, null, "error");
            closeEditBatchModal();
        }
    }

    function closeEditBatchModal() {
        document.getElementById('modalEditBatch').classList.remove('show');
        editBatchData = null;
    }

    function onEditStepOneChange() {
        if (!editBatchData) return;
        const target = parseInt(document.getElementById('editTargetPorsi').value || '0', 10);
        const date = document.getElementById('editProductionDate').value;
        if (target <= 0 || !date) return;

        // Current usages values
        let currentUsages = [];
        document.querySelectorAll('#editMaterialBlocksContainer .qty-usage-input').forEach(inp => {
            const lotId = parseInt(inp.getAttribute('data-lot-id'), 10);
            const val = parseFloat(inp.value) || 0;
            if (val > 0) {
                currentUsages.push({ material_lot_id: lotId, qty_used: val });
            }
        });

        fetchEditRecipeMaterials(editBatchData.menu_id, editBatchData.ukuran_porsi, target, date, currentUsages);
    }

    async function fetchEditRecipeMaterials(menuId, ukuran, target, date, prefilledUsages) {
        const loader = document.getElementById('editLoadingBlocks');
        const container = document.getElementById('editMaterialBlocksContainer');
        loader.style.display = 'block';
        container.innerHTML = '';

        try {
            const res = await fetch(`/staff/batch/recipe-materials?menu_id=${menuId}&ukuran_porsi=${ukuran}&target_porsi=${target}&production_date=${date}`);
            const data = await res.json();
            loader.style.display = 'none';

            if (!data.success) {
                container.innerHTML = `<div class="alert alert-error" style="background:#FEE2E2; color:#B91C1C; padding:12px; border-radius:8px; font-size:12.5px;">${data.message}</div>`;
                return;
            }

            editCurrentBlocks = data.blocks || [];
            renderEditMaterialBlocks(prefilledUsages);
        } catch (e) {
            loader.style.display = 'none';
            container.innerHTML = `<div class="alert alert-error">Terjadi kesalahan: ${e.message}</div>`;
        }
    }

    function renderEditMaterialBlocks(prefilledUsages = []) {
        const container = document.getElementById('editMaterialBlocksContainer');
        container.innerHTML = '';

        const usageMap = {};
        prefilledUsages.forEach(u => {
            usageMap[u.material_lot_id] = u.qty_used;
        });

        editCurrentBlocks.forEach((block) => {
            const card = document.createElement('div');
            card.className = 'material-block-card';
            card.style.padding = '14px 16px';
            card.style.marginBottom = '14px';

            let lotsHtml = '';
            if (!block.lots || block.lots.length === 0) {
                lotsHtml = `
                    <div style="text-align: center; padding: 16px; color: #DC2626; font-size: 12px; background: #FEF2F2; border-radius: 8px;">
                        Tidak ada lot tersedia untuk bahan ini sebelum tanggal produksi.
                    </div>
                `;
            } else {
                lotsHtml = `
                    <div class="table-responsive" style="margin-top: 8px;">
                        <table class="staff-table data-table" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th style="width: 130px; padding: 8px 10px;">Barcode</th>
                                    <th style="width: 120px; padding: 8px 10px;">No. Lot</th>
                                    <th style="width: 120px; padding: 8px 10px;">Kedaluwarsa</th>
                                    <th style="width: 100px; padding: 8px 10px;">Sisa Stok</th>
                                    <th style="min-width: 130px; padding: 8px 10px;">Pemasok</th>
                                    <th style="width: 140px; padding: 8px 10px;">Pakai (${block.satuan})</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${block.lots.map((lot, lIdx) => {
                                    const isExpired = lot.days_to_expiry !== null && lot.days_to_expiry < 0;
                                    const isNear = lot.is_near_expiry;
                                    const expiryBadge = isExpired
                                        ? `<span class="badge-status badge-expired"><span class="status-dot"></span>Kedaluwarsa</span>`
                                        : (isNear ? `<span class="badge-status badge-near-expired"><span class="status-dot"></span>H-${lot.days_to_expiry}</span>` : `<span style="color:#16A34A; font-weight:600;">${lot.expiry_date_formatted}</span>`);

                                    const initialVal = usageMap[lot.id] !== undefined ? usageMap[lot.id] : '';

                                    return `
                                        <tr class="${isExpired ? 'opacity-50' : ''}">
                                            <td style="font-family: monospace; font-weight: 600; padding: 8px 10px;">${lot.barcode}</td>
                                            <td style="padding: 8px 10px;">${lot.lot_number || '-'}</td>
                                            <td style="padding: 8px 10px;">${expiryBadge}</td>
                                            <td style="font-weight: 600; padding: 8px 10px;">${lot.current_qty} ${block.satuan}</td>
                                            <td style="color: #64748B; padding: 8px 10px;">${lot.supplier_name}</td>
                                            <td style="padding: 8px 10px;">
                                                <input type="number" 
                                                       name="usages[${lot.id}][qty_used]"
                                                       data-material-id="${block.material_id}"
                                                       data-lot-id="${lot.id}"
                                                       data-max-qty="${lot.current_qty}"
                                                       data-lot-idx="${lIdx}"
                                                       value="${initialVal}"
                                                       class="form-input qty-usage-input"
                                                       min="0"
                                                       max="${lot.current_qty}"
                                                       step="0.001"
                                                       placeholder="0.000"
                                                       ${isExpired ? 'disabled' : ''}
                                                       oninput="onEditQtyInput(this, ${block.material_id}, ${lot.current_qty})"
                                                       style="width: 100%; height: 32px; font-size: 12.5px; font-weight: 600; padding: 0 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                                                <input type="hidden" name="usages[${lot.id}][material_lot_id]" value="${lot.id}">
                                                <div id="edit_mwarn_${lot.id}" style="display:none; font-size: 11px; color: #DC2626; margin-top: 2px;"></div>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            card.innerHTML = `
                <div class="material-block-header" style="padding-bottom: 10px; margin-bottom: 10px;">
                    <div class="material-title-group">
                        <div class="material-avatar" style="width: 32px; height: 32px; font-size: 13px;">${block.material_name.charAt(0).toUpperCase()}</div>
                        <div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #071E49;">${block.material_name}</h4>
                            <div style="font-size: 11.5px; color: #64748B;">Resep: ${block.qty_per_porsi} ${block.satuan} / porsi</div>
                        </div>
                    </div>
                    <div class="material-pills-group">
                        <span class="pill-info" style="font-size: 11.5px; padding: 3px 8px;">Kebutuhan: <strong>${block.kebutuhan} ${block.satuan}</strong></span>
                        <span class="pill-info" id="edit_mselected_${block.material_id}" style="font-size: 11.5px; padding: 3px 8px;">Terpilih: <strong>0.000 ${block.satuan}</strong></span>
                        <span class="pill-kurang" id="edit_mstatusPill_${block.material_id}" style="font-size: 11.5px; padding: 3px 10px;">Kurang ${block.kebutuhan} ${block.satuan}</span>
                        ${block.lots && block.lots.length > 0 ? `
                            <button type="button" class="btn-fefo" onclick="autoFillEditFefo(${block.material_id})" style="font-size: 11.5px; padding: 4px 10px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                <span>Isi otomatis FEFO</span>
                            </button>
                        ` : ''}
                    </div>
                </div>
                ${lotsHtml}
            `;

            container.appendChild(card);
            updateEditMaterialBlockStatus(block.material_id);
        });

        updateEditOverallSummary();
    }

    function onEditQtyInput(input, materialId, maxQty) {
        const val = parseFloat(input.value) || 0;
        const warnDiv = document.getElementById(`edit_mwarn_${input.getAttribute('data-lot-id')}`);

        if (val > maxQty) {
            warnDiv.style.display = 'block';
            warnDiv.textContent = `Stok lot hanya ${maxQty}`;
            input.style.borderColor = '#DC2626';
        } else {
            warnDiv.style.display = 'none';
            input.style.borderColor = '#CBD5E1';
        }

        updateEditMaterialBlockStatus(materialId);
        updateEditOverallSummary();
    }

    function updateEditMaterialBlockStatus(materialId) {
        const block = editCurrentBlocks.find(b => b.material_id === materialId);
        if (!block) return;

        const inputs = document.querySelectorAll(`#editMaterialBlocksContainer .qty-usage-input[data-material-id="${materialId}"]`);
        let totalUsed = 0;
        inputs.forEach(inp => totalUsed += (parseFloat(inp.value) || 0));

        totalUsed = Math.round(totalUsed * 1000) / 1000;
        const pillSelected = document.getElementById(`edit_mselected_${materialId}`);
        const pillStatus = document.getElementById(`edit_mstatusPill_${materialId}`);

        if (pillSelected) pillSelected.innerHTML = `Terpilih: <strong>${totalUsed.toFixed(3)} ${block.satuan}</strong>`;

        const diff = Math.round((block.kebutuhan - totalUsed) * 1000) / 1000;
        if (pillStatus) {
            if (diff <= 0) {
                pillStatus.className = 'pill-terpenuhi';
                pillStatus.innerHTML = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Terpenuhi</span>`;
            } else {
                pillStatus.className = 'pill-kurang';
                pillStatus.innerHTML = `Kurang ${diff.toFixed(3)} ${block.satuan}`;
            }
        }
    }

    function autoFillEditFefo(materialId) {
        const block = editCurrentBlocks.find(b => b.material_id === materialId);
        if (!block) return;

        let needed = block.kebutuhan;
        const inputs = Array.from(document.querySelectorAll(`#editMaterialBlocksContainer .qty-usage-input[data-material-id="${materialId}"]`));

        inputs.forEach(input => {
            if (input.disabled) return;
            const max = parseFloat(input.getAttribute('data-max-qty') || '0');

            if (needed <= 0) {
                input.value = '';
            } else if (max <= needed) {
                input.value = max.toFixed(3);
                needed = Math.round((needed - max) * 1000) / 1000;
            } else {
                input.value = needed.toFixed(3);
                needed = 0;
            }
            input.dispatchEvent(new Event('input'));
        });
    }

    function updateEditOverallSummary() {
        let allSatisfied = true;
        let shortages = [];

        editCurrentBlocks.forEach(b => {
            const inputs = document.querySelectorAll(`#editMaterialBlocksContainer .qty-usage-input[data-material-id="${b.material_id}"]`);
            let total = 0;
            inputs.forEach(inp => total += (parseFloat(inp.value) || 0));

            const diff = Math.round((b.kebutuhan - total) * 1000) / 1000;
            if (diff > 0) {
                allSatisfied = false;
                shortages.push(`${b.material_name} kurang ${diff} ${b.satuan}`);
            }
        });

        const summaryText = document.getElementById('editOverallShortageText');
        if (summaryText) {
            if (allSatisfied && editCurrentBlocks.length > 0) {
                summaryText.style.color = '#16A34A';
                summaryText.textContent = 'Semua bahan resep telah terpenuhi!';
            } else if (shortages.length > 0) {
                summaryText.style.color = '#DC2626';
                summaryText.textContent = shortages.join(' • ');
            }
        }
    }

    function submitEditModalBatch(actionType) {
        document.getElementById('editModalFormAction').value = actionType;

        document.querySelectorAll('#modalEditBatch .qty-usage-input').forEach(input => {
            const val = parseFloat(input.value) || 0;
            if (val <= 0) {
                input.disabled = true;
                const hiddenInput = input.parentElement.querySelector('input[type="hidden"]');
                if (hiddenInput) hiddenInput.disabled = true;
            }
        });

        document.getElementById('editBatchForm').submit();
    }

    function confirmAndSubmitEditModalBatch() {
        let unfulfilled = [];
        editCurrentBlocks.forEach(b => {
            const inputs = document.querySelectorAll(`#editMaterialBlocksContainer .qty-usage-input[data-material-id="${b.material_id}"]`);
            let total = 0;
            inputs.forEach(inp => total += (parseFloat(inp.value) || 0));

            const diff = Math.round((b.kebutuhan - total) * 1000) / 1000;
            if (diff > 0) {
                unfulfilled.push(`Bahan ${b.material_name} masih kurang ${diff} ${b.satuan}`);
            }
        });

        if (unfulfilled.length > 0) {
            showStyledAlert("Konfirmasi Ditolak", "Semua bahan resep harus terpenuhi sebelum batch dapat dikonfirmasi.", unfulfilled, "warning");
            return;
        }

        showStyledConfirm(
            "Konfirmasi Batch Ini?",
            "Stok bahan akan langsung dipotong dan batch akan dikunci.",
            () => submitEditModalBatch('confirm'),
            "Ya, Konfirmasi Batch"
        );
    }

    /* =========================================================================
       TAMBAH BAHAN DI LUAR RESEP
       ========================================================================= */
    function openExtraMaterialModal(target = 'create') {
        extraMaterialTarget = target;
        document.getElementById('extraMaterialSelect').value = '';
        document.getElementById('modalExtraMaterial').classList.add('show');
    }

    function closeExtraMaterialModal() {
        document.getElementById('modalExtraMaterial').classList.remove('show');
    }

    async function confirmAddExtraMaterial() {
        const select = document.getElementById('extraMaterialSelect');
        const matId = parseInt(select.value, 10);
        if (!matId) {
            showStyledAlert('Pilihan Bahan Kosong', 'Silakan pilih bahan di luar resep terlebih dahulu.', null, 'warning');
            return;
        }

        const date = extraMaterialTarget === 'edit'
            ? document.getElementById('editProductionDate').value
            : document.getElementById('modalProductionDate').value;

        closeExtraMaterialModal();

        try {
            const res = await fetch(`/staff/batch/material-lots?material_id=${matId}&production_date=${date}`);
            const data = await res.json();
            if (!data.success) {
                showStyledAlert('Gagal Memuat Lot', data.message || 'Gagal memuat lot bahan.', null, 'error');
                return;
            }

            renderExtraMaterialBlock(data.material, data.lots, extraMaterialTarget);
        } catch (e) {
            showStyledAlert('Terjadi Kesalahan', e.message, null, 'error');
        }
    }

    function renderExtraMaterialBlock(material, lots, target = 'create') {
        const container = target === 'edit'
            ? document.getElementById('editExtraMaterialsContainer')
            : document.getElementById('modalExtraMaterialsContainer');

        const card = document.createElement('div');
        card.className = 'material-block-card';
        card.style.padding = '14px 16px';
        card.style.marginBottom = '14px';
        card.style.borderLeft = '3.5px solid #0284C7';

        let lotsHtml = '';
        if (!lots || lots.length === 0) {
            lotsHtml = `<div style="padding:12px; color:#64748B; font-size:12px; text-align:center;">Tidak ada stok lot tersedia.</div>`;
        } else {
            lotsHtml = `
                <div class="table-responsive" style="margin-top: 8px;">
                    <table class="staff-table data-table" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th style="width: 130px; padding: 8px 10px;">Barcode</th>
                                <th style="width: 120px; padding: 8px 10px;">No. Lot</th>
                                <th style="width: 120px; padding: 8px 10px;">Kedaluwarsa</th>
                                <th style="width: 100px; padding: 8px 10px;">Sisa Stok</th>
                                <th style="min-width: 130px; padding: 8px 10px;">Pemasok</th>
                                <th style="width: 140px; padding: 8px 10px;">Pakai (${material.satuan})</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${lots.map(lot => `
                                <tr>
                                    <td style="font-family: monospace; font-weight: 600; padding: 8px 10px;">${lot.barcode}</td>
                                    <td style="padding: 8px 10px;">${lot.lot_number || '-'}</td>
                                    <td style="padding: 8px 10px;">${lot.expiry_date_formatted}</td>
                                    <td style="font-weight: 600; padding: 8px 10px;">${lot.current_qty} ${material.satuan}</td>
                                    <td style="color: #64748B; padding: 8px 10px;">${lot.supplier_name}</td>
                                    <td style="padding: 8px 10px;">
                                        <input type="number" 
                                               name="usages[${lot.id}][qty_used]"
                                               class="form-input qty-usage-input"
                                               min="0"
                                               max="${lot.current_qty}"
                                               step="0.001"
                                               placeholder="0.000"
                                               style="width: 100%; height: 32px; font-size: 12.5px; font-weight: 600; padding: 0 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                                        <input type="hidden" name="usages[${lot.id}][material_lot_id]" value="${lot.id}">
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="material-block-header" style="padding-bottom: 8px; margin-bottom: 8px;">
                <div class="material-title-group">
                    <div class="material-avatar" style="width: 30px; height: 30px; font-size: 12px; background:#E0F2FE; color:#0284C7;">+</div>
                    <div>
                        <h4 style="margin: 0; font-size: 13.5px; font-weight: 700; color: #071E49;">${material.nama} <span class="chip-kategori" style="background:#E0F2FE; color:#0369A1; border:none; font-size:11px;">Bahan Luar Resep</span></h4>
                    </div>
                </div>
                <button type="button" class="btn-danger-outline" onclick="this.closest('.material-block-card').remove()" style="font-size:11.5px; padding:3px 8px;">Hapus</button>
            </div>
            ${lotsHtml}
        `;

        container.appendChild(card);
    }

    function openConfirmModal(id, code, menu, target) {
        const form = document.getElementById('formConfirmBatch');
        form.action = `/staff/batch/${id}/confirm`;
        document.getElementById('confirmBatchSubtitle').textContent = `${code} • Menu: ${menu} (${target} porsi)`;
        document.getElementById('modalConfirmBatch').classList.add('show');
    }

    function closeConfirmModal() {
        document.getElementById('modalConfirmBatch').classList.remove('show');
    }

    function openCancelModal(id, code) {
        const form = document.getElementById('formCancelBatch');
        form.action = `/staff/batch/${id}/cancel`;
        document.getElementById('cancelBatchSubtitle').textContent = `Batch: ${code}`;
        document.getElementById('modalCancelBatch').classList.add('show');
    }

    function closeCancelModal() {
        document.getElementById('modalCancelBatch').classList.remove('show');
    }
</script>
@endpush
@endsection
