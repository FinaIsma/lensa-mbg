@extends('layouts.staff')

@section('title', 'Hasil Produksi - Staff SPPG')

@section('content')
<div class="space-y-6 pb-12">
    {{-- Header Section --}}
    <div style="margin-bottom: 24px;">
        <div class="page-title-section" style="margin-bottom: 0;">
            <h1 class="page-title">Hasil Produksi</h1>
            <p class="page-subtitle">Verifikasi porsi matang siap saji, monitoring susut pengolahan, dan bukti foto sebelum distribusi.</p>
        </div>
    </div>

    {{-- 4 Stat Cards Grid --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Siap Dicatat Hasil --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Siap Dicatat</span>
                <span class="stat-value">{{ number_format($stats['siap_dicatat'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Batch selesai tahap olah</span>
            </div>
        </div>

        {{-- Card 2: Total Porsi Jadi --}}
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
                <span class="stat-label">Total Porsi Jadi</span>
                <span class="stat-value">{{ number_format($stats['total_porsi_jadi'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Porsi siap didistribusikan</span>
            </div>
        </div>

        {{-- Card 3: Keberhasilan Target --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Tingkat Efisiensi</span>
                <span class="stat-value">{{ $stats['persentase_keberhasilan'] }}%</span>
                <span class="stat-subtext">Realisasi terhadap target</span>
            </div>
        </div>

        {{-- Card 4: Total Susut (Loss) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-rose">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Susut (Loss)</span>
                <span class="stat-value">{{ number_format($stats['total_susut'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Porsi berkurang saat olah</span>
            </div>
        </div>
    </div>


    {{-- Banner Notifikasi Jika Ada Batch yang Siap Dicatat --}}
    @if ($eligibleBatches->isNotEmpty())
        <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #92400E;">
                        Terdapat {{ $eligibleBatches->count() }} batch pengolahan yang sudah selesai dan siap dicatat hasil porsinya!
                    </div>
                    <div style="font-size: 12px; color: #B45309; margin-top: 2px;">
                        Batch terbaru: <strong>{{ $eligibleBatches->first()->batch_code }}</strong> ({{ $eligibleBatches->first()->menu?->nama }}).
                    </div>
                </div>
            </div>
            <button type="button" onclick="openAddHasilModal()" class="btn-primary" style="background-color: #D97706; border-color: #D97706; padding: 7px 16px; font-size: 12.5px;">
                Catat Hasil Sekarang &rarr;
            </button>
        </div>
    @endif

    {{-- Unified Table Card Container Sesuai Standar Modul Pemasok --}}
    <div class="pemasok-table-card hasil-table-card">
        <form method="GET" action="{{ route('staff.hasil.index') }}" class="pemasok-filter-bar hasil-filter-bar">
            {{-- Search Box (Kiri) --}}
            <div class="pemasok-search-box hasil-search-box">
                <svg class="pemasok-search-icon hasil-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode batch, menu..." class="pemasok-search-input hasil-search-input">
            </div>

            <div class="pemasok-actions-right hasil-actions-right">
                {{-- Dropdown Status Filter: Semua / Ada Susut / Tanpa Susut / Hari Ini --}}
                <div class="pemasok-select-wrapper hasil-select-wrapper">
                    <select name="status" class="pemasok-select hasil-select" onchange="this.form.submit()">
                        <option value="" {{ in_array(strtolower($status ?? ''), ['', 'semua'], true) ? 'selected' : '' }}>Semua</option>
                        <option value="susut" {{ ($status ?? '') === 'susut' ? 'selected' : '' }}>Ada Susut</option>
                        <option value="sesuai" {{ ($status ?? '') === 'sesuai' ? 'selected' : '' }}>Tanpa Susut</option>
                        <option value="hari_ini" {{ ($status ?? '') === 'hari_ini' ? 'selected' : '' }}>Hari Ini</option>
                    </select>
                    <svg class="pemasok-select-chevron hasil-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Tombol + Catat Hasil Baru (Kanan) --}}
                <button type="button" class="btn-tambah-pemasok btn-catat-hasil" onclick="openAddHasilModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Catat Hasil Baru</span>
                </button>
            </div>
        </form>

        {{-- Legend Keterangan Warna Status Produksi --}}
        <div class="hasil-legend-bar">
            <div class="hasil-legend-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <span>Keterangan Status:</span>
            </div>
            <div class="hasil-legend-list">
                <div class="hasil-legend-item" title="Batch dalam tahap pengolahan atau siap didistribusikan">
                    <span class="legend-color-dot is-ordered"></span>
                    <span class="legend-text"><strong>Kuning:</strong> Pengolahan / Siap Distribusi</span>
                </div>
                <div class="hasil-legend-item" title="Batch telah selesai diproduksi dan didistribusikan">
                    <span class="legend-color-dot is-received"></span>
                    <span class="legend-text"><strong>Hijau:</strong> Selesai</span>
                </div>
                <div class="hasil-legend-item" title="Draft batch produksi baru">
                    <span class="legend-color-dot is-draft"></span>
                    <span class="legend-text"><strong>Abu-abu:</strong> Draft</span>
                </div>
                <div class="hasil-legend-item" title="Batch produksi dibatalkan">
                    <span class="legend-color-dot is-cancelled"></span>
                    <span class="legend-text"><strong>Merah:</strong> Dibatalkan</span>
                </div>
            </div>
        </div>

        {{-- Table Container Sesuai Standar Modul Pemasok (Fit 100%, Tanpa Scroll Horizontal) --}}
        <div class="pemasok-table-responsive hasil-table-responsive">
            <table class="pemasok-table hasil-table">
                <thead>
                    <tr>
                        <th class="col-batch">Batch</th>
                        <th class="col-menu">Menu</th>
                        <th class="col-target is-center">Target</th>
                        <th class="col-ready is-center">Porsi Siap</th>
                        <th class="col-susut is-center">Susut</th>
                        <th class="col-kondisi">Uji Fisik &amp; Kondisi</th>
                        <th class="col-ahli-gizi">Ahli Gizi</th>
                        <th class="col-waktu">Waktu QC &amp;<br>Batas Kritis</th>
                        <th class="col-status is-center">Status</th>
                        <th class="col-aksi is-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php
                            $check = $batch->preDistributionCheck;
                            $porsiSiap = $batch->actual_porsi ?? $check?->portions_ready ?? 0;
                            $susut = max(0, $batch->target_porsi - $porsiSiap);
                            $evidence = $check?->evidences->first();
                            $hasRecordedLoss = isset($recordedLossBatchIds[$batch->id]);
                            $nutritionistName = $check?->effective_nutritionist_name ?? ($check?->checker?->nama ?? 'Tenaga Gizi');
                            $checkedAtStr = $check?->checked_at ? $check->checked_at->format('d/m/Y, H:i') . ' WIB' : '-';
                            $estimatedDistStr = $check?->estimated_distribution_at ? $check->estimated_distribution_at->format('H:i') . ' WIB' : '-';
                            $consumeByStr = $check?->consume_by ? $check->consume_by->format('H:i') . ' WIB' : 'Maks. 4 Jam Pasca Masak';
                            $formTypeLabel = $check?->form_type_label ?? 'Makanan Siap Santap';
                        @endphp
                        <tr>
                            {{-- 1. Batch (Format Sempit & Adaptif) --}}
                            <td class="col-batch">
                                <a href="{{ route('staff.batches.show', $batch->id) }}" class="pemasok-name-link hasil-batch-link" title="Detail Batch: {{ $batch->batch_code }}">
                                    {!! str_replace('-', '-<wbr>', e($batch->batch_code)) !!}
                                </a>
                            </td>

                            {{-- 2. Menu --}}
                            <td class="col-menu">
                                <div class="hasil-menu-name">{{ $batch->menu?->nama ?? '-' }}</div>
                                <span class="chip-porsi {{ $batch->recipe?->ukuran_porsi === 'besar' ? 'chip-porsi-besar' : 'chip-porsi-kecil' }}" style="margin-top: 3px; display: inline-block;">
                                    Porsi {{ ucfirst($batch->recipe?->ukuran_porsi ?? 'Standar') }}
                                </span>
                            </td>

                            {{-- 3. Target --}}
                            <td class="col-target is-center" style="color: #475569;">
                                {{ number_format($batch->target_porsi, 0, ',', '.') }} ps
                            </td>

                            {{-- 4. Porsi Siap --}}
                            <td class="col-ready is-center" style="font-weight: 600; color: #0F172A;">
                                <span>{{ number_format($porsiSiap, 0, ',', '.') }} ps</span>
                                @if ($porsiSiap > $batch->target_porsi)
                                    <div class="hasil-surplus-tag" style="margin-top: 2px;" title="Porsi siap melebihi target awal">
                                        +{{ number_format($porsiSiap - $batch->target_porsi, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>

                            {{-- 5. Susut --}}
                            <td class="col-susut is-center">
                                @if ($susut > 0)
                                    <span class="chip-susut-loss">
                                        {{ number_format($susut, 0, ',', '.') }} ps
                                    </span>
                                @else
                                    <span class="chip-susut-zero">
                                        0 ps
                                    </span>
                                @endif
                            </td>

                            {{-- 6. Uji Fisik & Kondisi --}}
                            <td class="col-kondisi">
                                <div class="hasil-qc-card">
                                    <div class="hasil-qc-badges">
                                        <span class="hasil-qc-status-badge {{ $check?->physical_test_status === 'failed' ? 'is-failed' : ($check?->physical_test_status === 'warning' ? 'is-warning' : 'is-passed') }}">
                                            {{ ($check?->physical_test_status === 'failed') ? 'Tidak Lolos' : (($check?->physical_test_status === 'warning') ? 'Bersyarat' : 'Lolos Uji Fisik') }}
                                        </span>
                                        <span class="hasil-qc-category-tag" title="{{ $formTypeLabel }}">
                                            {{ $formTypeLabel }}
                                        </span>
                                    </div>
                                    <div class="hasil-qc-condition-text" title="{{ $check?->reported_condition ?? '-' }}">
                                        {{ $check?->reported_condition ?? '-' }}
                                    </div>
                                    @if ($check?->note)
                                        <div class="hasil-qc-note-box" title="{{ $check->note }}">
                                            <span class="hasil-qc-note-label">Catatan:</span> {{ $check->note }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- 7. Ahli Gizi (Sempit & Ke Bawah jika panjang) --}}
                            <td class="col-ahli-gizi">
                                <div class="hasil-nutritionist-name" title="{{ $nutritionistName }}">
                                    {{ $nutritionistName }}
                                </div>
                                <div class="hasil-checker-sub" title="Pencatat QC: {{ $check?->checker?->nama ?? '-' }}">
                                    QC: {{ $check?->checker?->nama ?? '-' }}
                                </div>
                            </td>

                            {{-- 8. Waktu QC & Batas Kritis --}}
                            <td class="col-waktu">
                                <div style="font-size: 11.5px; color: #1E293B; line-height: 1.25;">
                                    <span style="color: #64748B;">QC:</span> {{ $check?->checked_at ? $check->checked_at->format('H:i') . ' WIB' : '-' }}
                                </div>
                                @if ($check?->consume_by)
                                    <div style="margin-top: 2px;">
                                        <span style="display: inline-flex; align-items: center; gap: 3px; font-size: 10.5px; font-weight: 700; color: #B91C1C; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 4px; padding: 1px 5px;">
                                            <span>Maks:</span> {{ $check->consume_by->format('H:i') }} WIB
                                        </span>
                                    </div>
                                @endif
                                @if ($check?->checked_at)
                                    <div style="font-size: 10px; color: #94A3B8; margin-top: 2px;">
                                        {{ $check->checked_at->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>

                            {{-- 9. Status (Compact Indicator dengan Keterangan di Atas Tabel) --}}
                            <td class="col-status is-center">
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    <span class="chip-status badge-status badge-compact {{ $batch->status_badge_class }}" title="Status: {{ $batch->status_label }}" aria-label="{{ $batch->status_label }}">
                                        <span class="chip-dot status-dot"></span>
                                        <span class="sr-only">{{ $batch->status_label }}</span>
                                    </span>
                                </div>
                            </td>

                            {{-- 10. Aksi: Lihat Detail QC & Bukti --}}
                            <td class="col-aksi is-center" style="white-space: nowrap;">
                                <div class="pemasok-actions-group action-buttons-group" style="justify-content: center;">
                                    <button type="button" 
                                            class="btn-pemasok-detail btn-action-icon" 
                                            onclick="showEvidencePreview(
                                                '{{ $evidence ? $evidence->url : '' }}',
                                                '{{ $batch->batch_code }}',
                                                '{{ addslashes($check?->reported_condition ?? '') }}',
                                                {{ ($evidence && $evidence->is_public) ? 'true' : 'false' }},
                                                '{{ $checkedAtStr }}',
                                                '{{ $estimatedDistStr }}',
                                                '{{ $consumeByStr }}',
                                                '{{ addslashes($nutritionistName) }}',
                                                '{{ addslashes($formTypeLabel) }}',
                                                '{{ addslashes($check?->note ?? '') }}'
                                            )"
                                            title="Lihat Detail Hasil, Uji Fisik &amp; Batas Kritis"
                                            aria-label="Lihat Detail">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="hasil-empty-cell" style="text-align: center; padding: 40px 20px;">
                                <div class="hasil-empty-state">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="hasil-empty-icon" style="color: #94A3B8; margin: 0 auto 8px;">
                                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                    </svg>
                                    <div style="font-size: 14px; font-weight: 600; color: #334155;">Belum ada hasil produksi yang dicatat.</div>
                                    <div style="font-size: 12px; color: #94A3B8; margin-top: 2px;">Klik tombol "Catat Hasil Baru" di atas untuk mencatat porsi dari batch yang telah selesai diolah.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('modals')
    @include('staff.hasil.partials.modals')
@endsection

@push('scripts')
<script>
    // Prompt otomatis catat susut jika baru saja simpan hasil dengan susut > 0
    @if (session('prompt_susut_batch_id'))
        document.addEventListener('DOMContentLoaded', function() {
            openCatatSusutModal(
                {{ session('prompt_susut_batch_id') }},
                '{{ session('prompt_susut_batch_code') }}',
                {{ session('prompt_susut_qty') }}
            );
        });
    @endif

    // Otomatis buka modal catat hasil jika diarahkan dari pengolahan (batch_id di URL)
    @if (request('batch_id') || request('catat_batch_id'))
        document.addEventListener('DOMContentLoaded', function() {
            const targetBatchId = '{{ request('batch_id') ?: request('catat_batch_id') }}';
            if (typeof openAddHasilModal === 'function') {
                openAddHasilModal();
            }
            const sel = document.getElementById('hasil_batch_select');
            if (sel) {
                sel.value = targetBatchId;
                if (typeof handleHasilBatchChange === 'function') {
                    handleHasilBatchChange(sel);
                }
            }
        });
    @endif
</script>
@endpush

