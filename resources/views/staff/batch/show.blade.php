@extends('layouts.staff')

@section('title', 'Detail Batch ' . $batch->batch_code . ' - Staff SPPG')

@section('content')
<div class="space-y-4 pb-12" style="max-width: 1040px; margin: 0 auto;">
    {{-- Top Header Navigation --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('staff.batches.index') }}" class="btn-action-icon" style="width: 32px; height: 32px;" title="Kembali ke Daftar Batch">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h1 class="page-title" style="margin: 0; font-size: 19px; font-weight: 700; color: #071E49;">
                        {{ $batch->batch_code }}
                    </h1>
                    <span class="badge-status {{ $batch->status_badge_class }}">
                        <span class="status-dot"></span>
                        <span>{{ $batch->status_label }}</span>
                    </span>
                    @if ($batch->is_locked)
                        <span title="Batch Terkunci" style="color: #64748B; display: inline-flex; align-items: center;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div style="display: flex; align-items: center; gap: 8px;">
            @if ($batch->status === 'draft')
                <a href="{{ route('staff.batches.edit', $batch->id) }}" class="btn-secondary" style="font-size: 12px; padding: 6px 12px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    <span>Ubah Batch</span>
                </a>
                <button type="button" onclick="openConfirmModal()" class="btn-success" style="font-size: 12px; padding: 6px 12px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Konfirmasi Batch</span>
                </button>
                <button type="button" onclick="openCancelModal()" class="btn-danger-outline" style="font-size: 12px; padding: 6px 12px;">
                    <span>Batalkan</span>
                </button>
            @elseif(in_array($batch->status, ['confirmed', 'processing']))
                <a href="{{ route('staff.pengolahan.index', ['batch_id' => $batch->id]) }}" class="btn-primary" style="font-size: 12px; padding: 6px 12px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2l4 4-10 10-4-4L18 2z"></path><path d="M14.5 5.5l4 4"></path><path d="M3 21l3-3"></path><path d="M6 18l-3 3"></path><line x1="8" y1="16" x2="4" y2="20"></line></svg>
                    <span>Buka Lembar Pengolahan</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Main Info Header Card --}}
    <div class="detail-card" style="padding: 14px 18px; margin-bottom: 16px; border-radius: 10px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px 16px;">
            <div>
                <span style="display: block; font-size: 11px; color: #64748B; font-weight: 500;">Tanggal Produksi</span>
                <span style="display: block; font-size: 13.5px; font-weight: 700; color: #071E49; margin-top: 2px;">
                    {{ $batch->production_date ? $batch->production_date->format('d F Y') : '-' }}
                </span>
            </div>

            <div>
                <span style="display: block; font-size: 11px; color: #64748B; font-weight: 500;">Menu & Kategori</span>
                <div style="margin-top: 2px;">
                    <span style="font-size: 13.5px; font-weight: 700; color: #071E49; display: block;">{{ $batch->menu?->nama }}</span>
                    <div style="margin-top: 4px;">
                        <span class="chip-kategori" style="font-size: 11px; padding: 2px 7px;">{{ $batch->menu?->kategori_label }}</span>
                    </div>
                </div>
            </div>

            <div>
                <span style="display: block; font-size: 11px; color: #64748B; font-weight: 500;">Ukuran Porsi (Resep v{{ $batch->recipe?->version ?? 1 }})</span>
                <span style="display: inline-block; margin-top: 2px; font-size: 11px; padding: 2px 7px;" class="chip-porsi {{ $batch->recipe?->ukuran_porsi === 'besar' ? 'chip-porsi-besar' : 'chip-porsi-kecil' }}">
                    Porsi {{ ucfirst($batch->recipe?->ukuran_porsi) }}
                </span>
            </div>

            <div>
                <span style="display: block; font-size: 11px; color: #64748B; font-weight: 500;">Target & Hasil Jadi</span>
                <div style="display: flex; align-items: baseline; gap: 6px; margin-top: 2px;">
                    <span style="font-size: 13.5px; font-weight: 700; color: #071E49;">
                        Target: {{ number_format($batch->target_porsi, 0, ',', '.') }}
                    </span>
                    <span style="font-size: 12px; color: #64748B;">/</span>
                    <span style="font-size: 13.5px; font-weight: 700; color: {{ $batch->actual_porsi !== null ? '#16A34A' : '#94A3B8' }};">
                        Hasil: {{ $batch->actual_porsi !== null ? number_format($batch->actual_porsi, 0, ',', '.') : 'Belum dicatat' }}
                    </span>
                </div>
            </div>

            <div>
                <span style="display: block; font-size: 11px; color: #64748B; font-weight: 500;">Total Biaya Bahan</span>
                <span style="display: block; font-size: 14.5px; font-weight: 700; color: #042A70; margin-top: 2px;">
                    {{ $batch->total_cost ? 'Rp ' . number_format($batch->total_cost, 0, ',', '.') : 'Menunggu konfirmasi' }}
                </span>
            </div>

            <div>
                <span style="display: block; font-size: 11px; color: #64748B; font-weight: 500;">Biaya per Porsi</span>
                <span style="display: block; font-size: 14.5px; font-weight: 700; color: #071E49; margin-top: 2px;">
                    @if ($batch->biaya_per_porsi !== null)
                        Rp {{ number_format($batch->biaya_per_porsi, 0, ',', '.') }}
                    @elseif($batch->estimasi_biaya_per_porsi !== null)
                        <span style="font-size: 12px; color: #64748B;">~Rp {{ number_format($batch->estimasi_biaya_per_porsi, 0, ',', '.') }} (est)</span>
                    @else
                        <span style="color: #94A3B8; font-size: 12px;">-</span>
                    @endif
                </span>
            </div>

            <div>
                <span style="display: block; font-size: 11px; color: #64748B; font-weight: 500;">Petugas Pembuat</span>
                <span style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-top: 2px;">
                    {{ $batch->creator?->nama ?? 'Staff SPPG' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="tabs-nav">
        <button type="button" class="tab-btn active" onclick="switchTab('tab-kebutuhan', this)">
            1. Kebutuhan & Material
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('tab-pemakaian', this)">
            2. Pemakaian Lot (Biaya)
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('tab-pengolahan', this)">
            3. Pengolahan & Hasil
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('tab-pengiriman', this)">
            4. Pengiriman
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('tab-limbah', this)">
            5. Limbah
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('tab-penelusuran', this)">
            6. Penelusuran (Traceability)
        </button>
    </div>

    {{-- TAB 1: Kebutuhan dan Material --}}
    <div id="tab-kebutuhan" class="tab-content-panel active">
        <div class="detail-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                <h2 class="detail-card-title" style="margin-bottom: 0;">Komposisi Resep & Pemenuhan Bahan</h2>
                @if ($batch->status === 'draft')
                    <span class="badge-status badge-draft">Mode Draft: Alokasi dapat diubah</span>
                @else
                    <span class="badge-status badge-received">Terkunci & Dipotong dari Stok</span>
                @endif
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="min-width: 180px;">Nama Bahan</th>
                            <th style="min-width: 130px;">Kebutuhan Resep</th>
                            <th style="min-width: 130px;">Total Dialokasikan</th>
                            <th style="min-width: 120px;">Status Pemenuhan</th>
                            <th style="min-width: 160px;">Lot yang Dipakai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $recipeItems = $batch->recipe?->items ?? collect();
                            $usagesGrouped = $batch->materialUsages->groupBy(fn ($u) => $u->lot?->material_id);
                        @endphp

                        @forelse ($recipeItems as $idx => $ri)
                            @php
                                $required = round(((float) $ri->qty_per_porsi) * $batch->target_porsi, 3);
                                $usagesForMat = $usagesGrouped->get($ri->material_id) ?? collect();
                                $allocated = round((float) $usagesForMat->sum('qty_used'), 3);
                                $satuan = $ri->satuan ?: ($ri->material?->satuan ?: 'kg');
                                $isFulfilled = $allocated >= $required;
                            @endphp
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td style="font-weight: 600; color: #071E49;">
                                    {{ $ri->material?->nama }}
                                    <div style="font-size: 11.5px; color: #64748B; font-weight: normal;">
                                        Takaran: {{ (float) $ri->qty_per_porsi }} {{ $satuan }} / porsi
                                    </div>
                                </td>
                                <td style="font-weight: 700; color: #071E49;">
                                    {{ $required }} {{ $satuan }}
                                </td>
                                <td style="font-weight: 700; color: {{ $isFulfilled ? '#16A34A' : '#DC2626' }};">
                                    {{ $allocated }} {{ $satuan }}
                                </td>
                                <td>
                                    @if ($isFulfilled)
                                        <span class="pill-terpenuhi">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            Terpenuhi
                                        </span>
                                    @else
                                        <span class="pill-kurang">
                                            Kurang {{ round($required - $allocated, 3) }} {{ $satuan }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($usagesForMat->isEmpty())
                                        <span style="color: #94A3B8; font-size: 12px;">Belum ada lot dipilih</span>
                                    @else
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            @foreach ($usagesForMat as $u)
                                                <div style="font-size: 12px; color: #334155;">
                                                    <span style="font-family: monospace; font-weight: 600;">{{ $u->lot?->barcode }}</span>: 
                                                    <strong>{{ (float) $u->qty_used }} {{ $satuan }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94A3B8; padding: 24px;">Tidak ada item bahan dalam resep ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TAB 2: Pemakaian Lot & Rincian Biaya --}}
    <div id="tab-pemakaian" class="tab-content-panel">
        <div class="detail-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                <div>
                    <h2 class="detail-card-title" style="margin-bottom: 0;">Rincian Pemakaian Lot & Biaya Bahan</h2>
                    <p style="margin: 2px 0 0; font-size: 12.5px; color: #64748B;">Satu lot hanya dipakai satu baris per batch. Snapshot biaya dicatat saat konfirmasi.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="min-width: 140px;">Barcode Lot</th>
                            <th style="min-width: 130px;">Nomor Lot</th>
                            <th style="min-width: 160px;">Nama Bahan</th>
                            <th style="min-width: 120px; text-align: right;">Jumlah Digunakan</th>
                            <th style="min-width: 140px; text-align: right;">Biaya Satuan (Snapshot)</th>
                            <th style="min-width: 140px; text-align: right;">Total Biaya Baris</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sumBiaya = 0; @endphp
                        @forelse ($batch->materialUsages as $i => $usage)
                            @php
                                $lot = $usage->lot;
                                $unitCost = (float) ($usage->unit_cost_snapshot ?? $lot?->unit_cost ?? 0);
                                $rowCost = round(((float) $usage->qty_used) * $unitCost, 2);
                                $sumBiaya += $rowCost;
                                $satuan = $lot?->material?->satuan ?: 'kg';
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td style="font-family: monospace; font-weight: 700; color: #042A70;">
                                    <a href="{{ route('staff.lots.show', $lot?->id ?? 0) }}" style="color: inherit; text-decoration: none;">
                                        {{ $lot?->barcode ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ $lot?->lot_number ?? '-' }}</td>
                                <td style="font-weight: 600; color: #071E49;">{{ $lot?->material?->nama ?? '-' }}</td>
                                <td style="text-align: right; font-weight: 600;">
                                    {{ (float) $usage->qty_used }} {{ $satuan }}
                                </td>
                                <td style="text-align: right; color: #475569;">
                                    Rp {{ number_format($unitCost, 0, ',', '.') }} / {{ $satuan }}
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #071E49;">
                                    Rp {{ number_format($rowCost, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: #94A3B8; padding: 24px;">Belum ada baris pemakaian lot yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background: #F8FAFC; font-weight: 700;">
                            <td colspan="6" style="text-align: right; padding: 14px 16px; font-size: 14px; color: #071E49;">
                                TOTAL BIAYA BAHAN BAKU:
                            </td>
                            <td style="text-align: right; padding: 14px 16px; font-size: 15px; color: #042A70;">
                                Rp {{ number_format($sumBiaya, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- TAB 3: Pengolahan dan Hasil --}}
    <div id="tab-pengolahan" class="tab-content-panel">
        <div class="detail-card" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                <h2 class="detail-card-title" style="margin-bottom: 0;">Linimasa Tahap Pengolahan</h2>
                @if (in_array($batch->status, ['confirmed', 'processing']))
                    <a href="{{ route('staff.pengolahan.index', ['batch_id' => $batch->id]) }}" class="btn-primary" style="font-size: 12px; padding: 6px 12px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 2l4 4-10 10-4-4L18 2z"></path><path d="M14.5 5.5l4 4"></path><path d="M3 21l3-3"></path><path d="M6 18l-3 3"></path><line x1="8" y1="16" x2="4" y2="20"></line></svg>
                        <span>Kelola Tahap Pengolahan</span>
                    </a>
                @endif
            </div>

            @if ($batch->processingSteps->isEmpty())
                <div style="text-align: center; padding: 32px; color: #64748B; background: #F8FAFC; border-radius: 10px;">
                    Belum ada tahap pengolahan yang dicatat untuk batch ini.
                </div>
            @else
                <div class="timeline-vertical">
                    @foreach ($batch->processingSteps as $step)
                        <div class="timeline-step-item">
                            <div class="timeline-step-node {{ $step->status }}">
                                @if ($step->status === 'done')
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                @endif
                            </div>
                            <div class="timeline-step-body">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                    <div style="font-weight: 700; font-size: 14.5px; color: #071E49;">
                                        {{ $step->step_name }}
                                    </div>
                                    <span class="badge-status {{ $step->status_badge_class }}">
                                        <span class="status-dot"></span>
                                        <span>{{ $step->status_label }}</span>
                                    </span>
                                </div>
                                <div style="font-size: 12px; color: #64748B; margin-top: 4px;">
                                    Mulai: <strong>{{ $step->started_at ? $step->started_at->format('d/m/Y H:i') : '-' }}</strong>
                                    @if ($step->ended_at)
                                        • Selesai: <strong>{{ $step->ended_at->format('d/m/Y H:i') }}</strong>
                                        • Durasi: <strong>{{ $step->duration_human }}</strong>
                                    @endif
                                    • Dicatat oleh: <em>{{ $step->recorder?->nama ?? 'Staff' }}</em>
                                </div>
                                @if ($step->note)
                                    <div style="font-size: 13px; color: #334155; margin-top: 8px; background: #FFFFFF; padding: 8px 12px; border-radius: 6px; border: 1px solid #E2E8F0;">
                                        {{ $step->note }}
                                    </div>
                                @endif
                                @if ($step->evidences->isNotEmpty())
                                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                                        @foreach ($step->evidences as $ev)
                                            <a href="{{ asset('storage/' . $ev->file_path) }}" target="_blank" style="display: inline-block; border-radius: 6px; overflow: hidden; border: 1px solid #CBD5E1;">
                                                <img src="{{ asset('storage/' . $ev->file_path) }}" alt="Bukti" style="width: 70px; height: 50px; object-fit: cover;">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Hasil Porsi Jadi (Pre-Distribution Check) --}}
        <div class="detail-card">
            <h2 class="detail-card-title">Hasil Porsi & Uji Kualitas</h2>
            @if ($batch->actual_porsi !== null)
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 20px;">
                    <div>
                        <span style="font-size: 12px; color: #166534; font-weight: 500;">Porsi Siap Distribusi:</span>
                        <div style="font-size: 24px; font-weight: 800; color: #15803D; margin-top: 4px;">
                            {{ number_format($batch->actual_porsi, 0, ',', '.') }} porsi
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #166534; font-weight: 500;">Susut Produksi:</span>
                        <div style="font-size: 20px; font-weight: 700; color: #B45309; margin-top: 4px;">
                            {{ number_format($batch->susut_produksi ?? 0, 0, ',', '.') }} porsi
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #166534; font-weight: 500;">Kondisi Dilaporkan:</span>
                        <div style="font-size: 14px; font-weight: 600; color: #1E293B; margin-top: 4px;">
                            {{ $batch->preDistributionCheck?->reported_condition ?? 'Baik / Siap Saji' }}
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #166534; font-weight: 500;">Pemeriksa:</span>
                        <div style="font-size: 13.5px; font-weight: 600; color: #1E293B; margin-top: 4px;">
                            {{ $batch->preDistributionCheck?->checker?->nama ?? 'Staff Pengawas' }}
                        </div>
                    </div>
                </div>
            @else
                <div style="text-align: center; padding: 24px; color: #64748B; background: #F8FAFC; border-radius: 10px;">
                    Hasil porsi belum dicatat. Pencatatan hasil dapat dilakukan di laman Hasil setelah semua tahapan pengolahan selesai.
                </div>
            @endif
        </div>
    </div>

    {{-- TAB 4: Pengiriman --}}
    <div id="tab-pengiriman" class="tab-content-panel">
        <div class="detail-card">
            <h2 class="detail-card-title">Daftar Pengiriman Batch</h2>
            <div style="text-align: center; padding: 36px 20px; color: #64748B; background: #F8FAFC; border-radius: 10px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color: #94A3B8; margin-bottom: 10px;">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                <div style="font-weight: 600; font-size: 14px; color: #071E49;">Belum Ada Surat Jalan / Distribusi</div>
                <p style="font-size: 12.5px; margin: 4px 0 0;">Pengiriman batch ini ke titik sekolah / penerima akan tercatat otomatis saat pengiriman diterbitkan.</p>
            </div>
        </div>
    </div>

    {{-- TAB 5: Limbah & Susut --}}
    <div id="tab-limbah" class="tab-content-panel">
        <div class="detail-card">
            <h2 class="detail-card-title">Catatan Limbah & Susut Produksi</h2>
            @if ($batch->susut_produksi && $batch->susut_produksi > 0)
                <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; padding: 18px; margin-bottom: 16px;">
                    <div style="font-size: 14px; font-weight: 700; color: #B45309;">
                        Susut Produksi Terdeteksi: {{ $batch->susut_produksi }} porsi
                    </div>
                    <div style="font-size: 12.5px; color: #78350F; margin-top: 4px;">
                        Target: {{ $batch->target_porsi }} porsi • Hasil Jadi: {{ $batch->actual_porsi }} porsi.
                        Susut dicatat sebagai mutasi koreksi <em>PRODUCTION_LOSS</em> pada pembukuan porsi SPPG.
                    </div>
                </div>
            @else
                <div style="text-align: center; padding: 36px 20px; color: #64748B; background: #F8FAFC; border-radius: 10px;">
                    Tidak ada catatan limbah atau susut khusus untuk batch produksi ini.
                </div>
            @endif
        </div>
    </div>

    {{-- TAB 6: Penelusuran Mundur (Traceability) --}}
    <div id="tab-penelusuran" class="tab-content-panel">
        <div class="detail-card" style="margin-bottom: 24px;">
            <h2 class="detail-card-title">Penelusuran Rantai Pangan (Traceability)</h2>
            <p style="font-size: 13px; color: #64748B; margin-top: -8px; margin-bottom: 20px;">
                Penelusuran mundur dari batch produksi ke lot fisik bahan, penerimaan gudang, hingga toko pemasok.
            </p>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach ($batch->materialUsages as $u)
                    @php
                        $lot = $u->lot;
                        $rcv = $lot?->receivingItem?->receiving;
                        $po = $rcv?->purchase;
                        $supplier = $lot?->supplier ?? $po?->supplier;
                    @endphp
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #042A70; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                                    LOT
                                </div>
                                <div>
                                    <span style="font-weight: 700; font-size: 14.5px; color: #071E49;">{{ $lot?->material?->nama }}</span>
                                    <span style="font-size: 12px; color: #64748B;">• Barcode: <strong style="font-family: monospace;">{{ $lot?->barcode }}</strong></span>
                                </div>
                            </div>
                            <span class="pill-info">Digunakan: {{ (float) $u->qty_used }} {{ $lot?->material?->satuan ?: 'kg' }}</span>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 12.5px;">
                            <div>
                                <span style="color: #64748B; display: block;">No. Lot / Kedaluwarsa:</span>
                                <span style="font-weight: 600; color: #071E49;">{{ $lot?->lot_number ?: '-' }} (Exp: {{ $lot?->expiry_date ? $lot->expiry_date->format('d/m/Y') : '-' }})</span>
                            </div>
                            <div>
                                <span style="color: #64748B; display: block;">Surat Penerimaan:</span>
                                <span style="font-weight: 600; color: #071E49;">{{ $rcv?->receiving_no ?: '-' }} ({{ $rcv?->received_at ? $rcv->received_at->format('d/m/Y') : '-' }})</span>
                            </div>
                            <div>
                                <span style="color: #64748B; display: block;">Nota Pembelian (PO):</span>
                                <span style="font-weight: 600; color: #071E49;">{{ $po?->purchase_no ?: '-' }}</span>
                            </div>
                            <div>
                                <span style="color: #64748B; display: block;">Pemasok / Toko:</span>
                                <span style="font-weight: 600; color: #071E49;">{{ $supplier?->nama ?: '-' }} ({{ $supplier?->kontak ?: '-' }})</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Riwayat Audit Trail --}}
        <div class="detail-card">
            <h2 class="detail-card-title">Riwayat Aktivitas & Audit (Audit Trail)</h2>
            <div class="table-responsive">
                <table class="data-table" style="font-size: 12.5px;">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Waktu</th>
                            <th style="min-width: 130px;">Aktivitas</th>
                            <th style="min-width: 140px;">Pelaku</th>
                            <th style="min-width: 200px;">Detail Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($auditTrails as $audit)
                            <tr>
                                <td style="color: #64748B;">{{ $audit->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge-status {{ $audit->nada === 'ok' ? 'badge-received' : ($audit->nada === 'bad' ? 'badge-cancelled' : 'badge-draft') }}">
                                        {{ $audit->aktivitas_label }}
                                    </span>
                                </td>
                                <td style="font-weight: 600; color: #071E49;">{{ $audit->pelaku }}</td>
                                <td style="color: #334155; font-family: monospace; font-size: 11.5px;">
                                    @if ($audit->new_values)
                                        {{ json_encode($audit->new_values, JSON_UNESCAPED_UNICODE) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: #94A3B8; padding: 20px;">Belum ada catatan aktivitas audit.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Batch --}}
<div id="modalConfirmBatch" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #071E49;">Konfirmasi Batch Produksi</h3>
            <button type="button" onclick="closeConfirmModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('staff.batches.confirm', $batch->id) }}">
            @csrf
            <div class="modal-body-custom">
                <p style="font-size: 13.5px; color: #334155; margin-top: 0;">
                    Apakah Anda yakin ingin mengonfirmasi batch <strong>{{ $batch->batch_code }}</strong>?
                </p>
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; font-size: 12.5px; color: #475569;">
                    <ul style="margin: 0; padding-left: 18px;">
                        <li>Stok pada masing-masing lot terpilih akan <strong>langsung dipotong</strong>.</li>
                        <li>Harga per satuan bahan akan dikunci permanen.</li>
                        <li>Dokumen akan terkunci (is_locked = true) dan diteruskan ke Pengolahan.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" onclick="closeConfirmModal()" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-success">Ya, Konfirmasi & Potong Stok</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Batalkan Batch --}}
<div id="modalCancelBatch" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #071E49;">Batalkan Batch Produksi</h3>
            <button type="button" onclick="closeCancelModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('staff.batches.cancel', $batch->id) }}">
            @csrf
            <div class="modal-body-custom">
                <p style="font-size: 13.5px; color: #475569; margin: 0 0 14px;">
                    Batalkan draft batch <strong>{{ $batch->batch_code }}</strong>?
                </p>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Alasan Pembatalan <span style="color: #DC2626;">*</span></label>
                    <textarea name="reason" required rows="3" class="form-input" placeholder="Masukkan alasan..." style="width: 100%; font-size: 13px; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1;"></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" onclick="closeCancelModal()" class="btn-secondary">Kembali</button>
                <button type="submit" class="btn-danger-outline" style="background: #DC2626; color: #FFFFFF; border: none;">Ya, Batalkan Batch</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content-panel').forEach(p => p.classList.remove('active'));

        btn.classList.add('active');
        const panel = document.getElementById(tabId);
        if (panel) panel.classList.add('active');
    }

    function openConfirmModal() {
        document.getElementById('modalConfirmBatch').classList.add('show');
    }

    function closeConfirmModal() {
        document.getElementById('modalConfirmBatch').classList.remove('show');
    }

    function openCancelModal() {
        document.getElementById('modalCancelBatch').classList.add('show');
    }

    function closeCancelModal() {
        document.getElementById('modalCancelBatch').classList.remove('show');
    }
</script>
@endpush
@endsection
