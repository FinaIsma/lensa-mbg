@extends('layouts.staff')

@section('title', 'Pencatatan Limbah - Staff SPPG')

@section('content')
<div class="space-y-6 pb-12">
    {{-- Header Section --}}
    <div style="margin-bottom: 24px;">
        <div class="page-title-section" style="margin-bottom: 0;">
            <h1 class="page-title">Pencatatan Limbah</h1>
            <p class="page-subtitle">Monitoring data susut pengolahan, sisa porsi, dan pembuangan bahan.</p>
        </div>
    </div>

    {{-- 4 Stat Cards Grid (Ringkasan per Tipe) --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Susut Olah --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Susut Pengolahan</span>
                <span class="stat-value">{{ number_format($stats['susut_olah'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Porsi susut masak (Loss)</span>
            </div>
        </div>

        {{-- Card 2: Makanan Sisa (Food Waste) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-rose">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Makanan Sisa</span>
                <span class="stat-value">{{ number_format($stats['makanan_sisa'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Porsi sisa pasca distribusi</span>
            </div>
        </div>

        {{-- Card 3: Surplus Porsi --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Surplus Porsi</span>
                <span class="stat-value">{{ number_format($stats['surplus_porsi'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Porsi matang belum terkirim</span>
            </div>
        </div>

        {{-- Card 4: Bahan Dibuang (Material Disposal) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Bahan Dibuang</span>
                <span class="stat-value">{{ number_format($stats['bahan_dibuang_count'], 0, ',', '.') }}</span>
                <span class="stat-subtext">{{ number_format($stats['bahan_dibuang_qty'], 2, ',', '.') }} total qty lot rusak/exp</span>
            </div>
        </div>
    </div>

    {{-- Unified Table Card Container Sesuai Standar Modul Pemasok & Hasil --}}
    <div class="pemasok-table-card limbah-table-card">
        <form method="GET" action="{{ route('staff.limbah.index') }}" class="pemasok-filter-bar limbah-filter-bar">
            {{-- Search Box (Kiri) --}}
            <div class="pemasok-search-box limbah-search-box">
                <svg class="pemasok-search-icon limbah-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari alasan, batch, barcode lot..." class="pemasok-search-input limbah-search-input">
            </div>

            <div class="pemasok-actions-right limbah-actions-right">
                {{-- Dropdown Tipe Filter --}}
                <div class="pemasok-select-wrapper limbah-select-wrapper">
                    <select name="type" class="pemasok-select limbah-select" onchange="this.form.submit()">
                        <option value="" {{ empty($type) ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="PRODUCTION_LOSS" {{ $type === 'PRODUCTION_LOSS' ? 'selected' : '' }}>Susut Olah (Loss)</option>
                        <option value="FOOD_WASTE" {{ $type === 'FOOD_WASTE' ? 'selected' : '' }}>Makanan Sisa</option>
                        <option value="SURPLUS" {{ $type === 'SURPLUS' ? 'selected' : '' }}>Surplus Porsi</option>
                        <option value="MATERIAL_DISPOSAL" {{ $type === 'MATERIAL_DISPOSAL' ? 'selected' : '' }}>Bahan Dibuang</option>
                    </select>
                    <svg class="pemasok-select-chevron limbah-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Dropdown Status Filter --}}
                <div class="pemasok-select-wrapper limbah-select-wrapper" style="min-width: 120px;">
                    <select name="status" class="pemasok-select limbah-select" onchange="this.form.submit()">
                        <option value="" {{ empty($status) ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Voided</option>
                    </select>
                    <svg class="pemasok-select-chevron limbah-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Tombol + Catat Limbah (Kanan) --}}
                <button type="button" class="btn-tambah-pemasok btn-catat-limbah" onclick="openAddLimbahModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Catat Limbah</span>
                </button>
            </div>
        </form>

        {{-- Table Content: Sesuai Standar Modul Pemasok & Hasil (Fit 100%, Tanpa Scroll Horizontal) --}}
        <div class="pemasok-table-responsive limbah-table-responsive">
            <table class="pemasok-table limbah-table">
                <thead>
                    <tr>
                        <th class="col-waktu">Waktu</th>
                        <th class="col-tipe">Tipe</th>
                        <th class="col-batch">Batch atau Lot</th>
                        <th class="col-jumlah is-center">Jumlah</th>
                        <th class="col-alasan">Alasan</th>
                        <th class="col-pencatat">Pencatat</th>
                        <th class="col-status is-center">Status</th>
                        <th class="col-aksi is-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        @php
                            $isVoided = $record->status === 'voided';
                            $evidence = $record->evidences->first();
                            $hasPendingCorrection = $record->correctionRequests->where('status', 'pending')->isNotEmpty();
                        @endphp
                        <tr class="{{ $isVoided ? 'row-voided' : '' }}">
                            {{-- 1. Waktu (recorded_at) --}}
                            <td class="col-waktu">
                                <div style="font-size: 12.5px; font-weight: 500; color: #1E293B; line-height: 1.25;">
                                    {{ $record->recorded_at ? $record->recorded_at->format('d/m/Y') : '-' }}
                                </div>
                                @if ($record->recorded_at)
                                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                                        {{ $record->recorded_at->format('H:i') }} WIB
                                    </div>
                                @endif
                            </td>

                            {{-- 2. Tipe (Chip empat warna) --}}
                            <td class="col-tipe">
                                <span class="chip-status {{ $record->type_badge_class }}">
                                    {{ $record->type_label }}
                                </span>
                            </td>

                            {{-- 3. Batch atau Lot --}}
                            <td class="col-batch">
                                @if ($record->record_type === 'MATERIAL_DISPOSAL')
                                    <div>
                                        <a href="{{ route('staff.lots.show', $record->material_lot_id) }}" class="pemasok-name-link limbah-batch-link">
                                            {{ $record->lot?->lot_number ?? 'Lot' }}
                                        </a>
                                        <span style="font-size: 11px; color: #64748B;">({{ $record->lot?->barcode }})</span>
                                    </div>
                                    <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                                        {{ $record->lot?->material?->nama ?? '-' }}
                                    </div>
                                @else
                                    <div>
                                        @if ($record->batch)
                                            <a href="{{ route('staff.batches.show', $record->production_batch_id) }}" class="pemasok-name-link limbah-batch-link">
                                                {{ $record->batch->batch_code }}
                                            </a>
                                        @else
                                            <span style="color: #64748B;">-</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                                        {{ $record->batch?->menu?->nama ?? '-' }}
                                    </div>
                                    @if ($record->distribution)
                                        <div style="font-size: 11px; color: #2563EB; margin-top: 2px;">
                                            &rarr; Kirim: {{ $record->distribution->destination?->nama_tujuan }}
                                        </div>
                                    @endif
                                @endif
                            </td>

                            {{-- 4. Jumlah dengan Satuan --}}
                            <td class="col-jumlah is-center">
                                <div style="font-weight: 600; font-size: 13.5px; color: #0F172A;" class="{{ $isVoided ? 'voided-text' : '' }}">
                                    {{ rtrim(rtrim(number_format($record->qty, 3, ',', '.'), '0'), ',') }}
                                </div>
                                <div style="font-size: 11.5px; color: #64748B;">
                                    {{ $record->satuan }}
                                </div>
                            </td>

                            {{-- 5. Alasan --}}
                            <td class="col-alasan">
                                <div style="font-size: 13px; color: #1E293B; line-height: 1.35;" class="{{ $isVoided ? 'voided-text' : '' }}">
                                    {{ $record->reason ?? '-' }}
                                </div>
                            </td>

                            {{-- 6. Pencatat --}}
                            <td class="col-pencatat" style="color: #475569;">
                                {{ $record->recorder?->nama ?? 'Staff' }}
                            </td>

                            {{-- 7. Status (Active atau Voided) --}}
                            <td class="col-status is-center">
                                @if ($isVoided)
                                    <span class="chip-status badge-status badge-draft" title="Catatan telah dibatalkan via koreksi">
                                        <span class="chip-dot status-dot"></span>
                                        <span>Voided</span>
                                    </span>
                                @else
                                    <span class="chip-status badge-status badge-safe badge-received" title="Catatan sah">
                                        <span class="chip-dot status-dot"></span>
                                        <span>Active</span>
                                    </span>
                                @endif
                            </td>

                            {{-- 8. Aksi (Foto Bukti & Ajukan Koreksi) --}}
                            <td class="col-aksi is-center" style="white-space: nowrap;">
                                <div class="pemasok-actions-group action-buttons-group" style="justify-content: center; gap: 6px;">
                                    {{-- Foto Bukti --}}
                                    @if ($evidence)
                                        <button type="button" 
                                                class="btn-pemasok-detail btn-action-icon" 
                                                onclick="showEvidencePreview('{{ $evidence->url }}', 'Bukti {{ $record->type_label }}', '{{ addslashes($record->reason ?? '') }}')" 
                                                title="Lihat Foto Bukti" 
                                                aria-label="Lihat Foto Bukti">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- Ajukan Koreksi --}}
                                    @if (! $isVoided)
                                        @if ($hasPendingCorrection)
                                            <span class="chip-status badge-status badge-ordered" style="padding: 3px 8px; font-size: 11px;" title="Koreksi sedang ditinjau">
                                                <span class="chip-dot status-dot"></span>
                                                <span>Ditinjau</span>
                                            </span>
                                        @else
                                            <button type="button" 
                                                    onclick="openKoreksiLimbahModal({{ $record->id }}, '{{ $record->type_label }}', '{{ (float) $record->qty }} {{ $record->satuan }}')" 
                                                    class="btn-ajukan-koreksi" 
                                                    title="Ajukan pembatalan (void) ke Kepala SPPG">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                                <span>Ajukan Koreksi</span>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px 20px; color: #64748B;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #94A3B8;">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                    <span style="font-size: 14px; font-weight: 600; color: #334155;">Belum ada catatan limbah atau surplus.</span>
                                    <span style="font-size: 12px; color: #94A3B8;">Gunakan tombol "Catat Limbah" di atas untuk mendokumentasikan susut, sisa porsi, atau bahan rusak.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div style="padding: 16px 24px; border-top: 1px solid #F1F5F9;">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('modals')
    @include('staff.limbah.partials.modals')
@endsection

