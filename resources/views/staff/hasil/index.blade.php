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

    {{-- Unified Table Card Container --}}
    <div class="hasil-table-card">
        <form method="GET" action="{{ route('staff.hasil.index') }}" class="hasil-filter-bar">
            {{-- Search Box (Kiri) --}}
            <div class="hasil-search-box">
                <svg class="hasil-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode batch, menu..." class="hasil-search-input">
            </div>

            <div class="hasil-actions-right">
                {{-- Dropdown Status Filter: Semua / Ada Susut / Tanpa Susut / Hari Ini --}}
                <div class="hasil-select-wrapper">
                    <select name="status" class="hasil-select" onchange="this.form.submit()">
                        <option value="" {{ in_array(strtolower($status ?? ''), ['', 'semua'], true) ? 'selected' : '' }}>Semua</option>
                        <option value="susut" {{ ($status ?? '') === 'susut' ? 'selected' : '' }}>Ada Susut</option>
                        <option value="sesuai" {{ ($status ?? '') === 'sesuai' ? 'selected' : '' }}>Tanpa Susut</option>
                        <option value="hari_ini" {{ ($status ?? '') === 'hari_ini' ? 'selected' : '' }}>Hari Ini</option>
                    </select>
                    <svg class="hasil-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Tombol + Catat Hasil Baru (Kanan) --}}
                <button type="button" class="btn-catat-hasil" onclick="openAddHasilModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Catat Hasil Baru</span>
                </button>
            </div>
        </form>

        {{-- Table Content: daftar hasil (batch, menu, target, porsi siap, susut, kondisi, pencatat, waktu) --}}
        <div class="table-responsive">
            <table class="staff-table data-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 14%;">Batch</th>
                        <th style="width: 18%;">Menu</th>
                        <th style="width: 9%; text-align: center;">Target</th>
                        <th style="width: 11%; text-align: center;">Porsi Siap</th>
                        <th style="width: 12%; text-align: center;">Susut</th>
                        <th style="width: 20%;">Kondisi</th>
                        <th style="width: 8%;">Pencatat</th>
                        <th style="width: 8%; text-align: center;">Waktu</th>
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
                        @endphp
                        <tr>
                            {{-- 1. Batch --}}
                            <td>
                                <a href="{{ route('staff.batches.show', $batch->id) }}" style="font-weight: 700; color: #042A70; text-decoration: none;">
                                    {{ $batch->batch_code }}
                                </a>
                                <div style="margin-top: 3px;">
                                    <span class="badge-status {{ $batch->status_badge_class }}">
                                        <span class="status-dot"></span>
                                        <span>{{ $batch->status_label }}</span>
                                    </span>
                                </div>
                            </td>

                            {{-- 2. Menu --}}
                            <td>
                                <div style="font-weight: 600; color: #071E49;">{{ $batch->menu?->nama ?? '-' }}</div>
                                <div style="margin-top: 2px;">
                                    <span class="chip-porsi {{ $batch->recipe?->ukuran_porsi === 'besar' ? 'chip-porsi-besar' : 'chip-porsi-kecil' }}">
                                        Porsi {{ ucfirst($batch->recipe?->ukuran_porsi ?? 'Standar') }}
                                    </span>
                                </div>
                            </td>

                            {{-- 3. Target --}}
                            <td style="text-align: center; font-weight: 600; color: #071E49;">
                                {{ number_format($batch->target_porsi, 0, ',', '.') }} ps
                            </td>

                            {{-- 4. Porsi Siap (portions_ready) --}}
                            <td style="text-align: center;">
                                <span style="font-weight: 800; font-size: 14.5px; color: #15803D;">
                                    {{ number_format($porsiSiap, 0, ',', '.') }} ps
                                </span>
                                @if ($porsiSiap > $batch->target_porsi)
                                    <span style="font-size: 10.5px; color: #D97706; font-weight: 600; display: block;" title="Porsi siap melebihi target awal">
                                        +{{ number_format($porsiSiap - $batch->target_porsi, 0, ',', '.') }} surplus
                                    </span>
                                @endif
                            </td>

                            {{-- 5. Susut (Info otomatis: tidak boleh negatif + tombol Catat susut jika susut > 0) --}}
                            <td style="text-align: center;">
                                @if ($susut > 0)
                                    <span class="chip-susut-loss">
                                        {{ number_format($susut, 0, ',', '.') }} ps
                                    </span>
                                    <div style="margin-top: 5px;">
                                        @if (! $hasRecordedLoss)
                                            <button type="button" onclick="openCatatSusutModal({{ $batch->id }}, '{{ $batch->batch_code }}', {{ $susut }})" class="btn-catat-susut-inline" title="Buka form Limbah tipe PRODUCTION_LOSS">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                                <span>Catat Susut</span>
                                            </button>
                                        @else
                                            <span class="badge-susut-recorded" title="Susut telah tercatat di modul limbah">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                                <span>Limbah Dicatat</span>
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="chip-susut-zero">
                                        0 ps
                                    </span>
                                @endif
                            </td>

                            {{-- 6. Kondisi (reported_condition + note + foto bukti) --}}
                            <td>
                                <div style="font-size: 12.5px; color: #1E293B; line-height: 1.35;">
                                    {{ $check?->reported_condition ?? '-' }}
                                </div>
                                @if ($check?->note)
                                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                                        <em>Catatan:</em> {{ $check->note }}
                                    </div>
                                @endif
                                @if ($evidence)
                                    <div style="margin-top: 4px;">
                                        <button type="button" onclick="showEvidencePreview('{{ asset('storage/' . $evidence->file_path) }}', '{{ $batch->batch_code }}', '{{ addslashes($evidence->description ?? '') }}', {{ $evidence->is_public ? 'true' : 'false' }})" style="display: inline-flex; align-items: center; gap: 4px; background: #EFF6FF; border: 1px solid #DBEAFE; color: #1D4ED8; padding: 2px 7px; border-radius: 5px; font-size: 11px; cursor: pointer;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                                <circle cx="12" cy="13" r="4"></circle>
                                            </svg>
                                            <span>Foto Bukti {{ $evidence->is_public ? '(Publik)' : '' }}</span>
                                        </button>
                                    </div>
                                @endif
                            </td>

                            {{-- 7. Pencatat --}}
                            <td>
                                <div style="font-weight: 600; font-size: 12.5px; color: #071E49;">
                                    {{ $check?->checker?->nama ?? 'Staff' }}
                                </div>
                            </td>

                            {{-- 8. Waktu --}}
                            <td style="text-align: center;">
                                <div style="font-size: 12px; color: #334155; font-weight: 500;">
                                    {{ $check?->checked_at ? $check->checked_at->format('d/m/Y') : '-' }}
                                </div>
                                <div style="font-size: 11px; color: #94A3B8;">
                                    {{ $check?->checked_at ? $check->checked_at->format('H:i') : '' }} WIB
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 36px 20px; color: #64748B;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #94A3B8;">
                                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                    </svg>
                                    <span style="font-size: 13.5px; font-weight: 500;">Belum ada hasil produksi yang dicatat.</span>
                                    <span style="font-size: 12px; color: #94A3B8;">Klik tombol "Catat Hasil Baru" di atas untuk mencatat porsi dari batch yang telah selesai diolah.</span>
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
</script>
@endpush

