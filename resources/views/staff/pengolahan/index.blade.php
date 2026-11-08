@extends('layouts.staff')

@section('title', 'Pengolahan Produksi - Staff SPPG')

@section('content')
<div class="space-y-6 pb-12">
    {{-- Header Section --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div class="page-title-section" style="margin-bottom: 0;">
            <h1 class="page-title">Pengolahan Produksi</h1>
            <p class="page-subtitle">Pantau dan catat setiap tahapan kerja pemasakan, pengemasan, dan persiapan bahan secara berurutan atau paralel.</p>
        </div>
        <button type="button" onclick="openAddStepModal()" class="btn-primary" style="padding: 10px 20px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Tahap Kerja</span>
        </button>
    </div>

    {{-- 4 Stat Cards Grid --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Siap Diolah (Confirmed) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Siap Diolah</span>
                <span class="stat-value">{{ number_format($stats['siap_diolah'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Batch terkonfirmasi baru</span>
            </div>
        </div>

        {{-- Card 2: Sedang Diproses (Processing) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 2l4 4-10 10-4-4L18 2z"></path>
                    <path d="M14.5 5.5l4 4"></path>
                    <path d="M3 21l3-3"></path>
                    <path d="M6 18l-3 3"></path>
                    <line x1="8" y1="16" x2="4" y2="20"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Sedang Diproses</span>
                <span class="stat-value">{{ number_format($stats['sedang_diproses'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Tahapan sedang berjalan</span>
            </div>
        </div>

        {{-- Card 3: Tahap Selesai Hari Ini --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Tahap Selesai Hari Ini</span>
                <span class="stat-value">{{ number_format($stats['tahap_selesai_hari_ini'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Aktivitas dapur terlaksana</span>
            </div>
        </div>

        {{-- Card 4: Total Batch Aktif --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Batch Aktif</span>
                <span class="stat-value">{{ number_format($stats['total_aktif'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Antrean produksi hari ini</span>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="table-card" style="padding: 16px 20px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('staff.pengolahan.index') }}" style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748B; margin-bottom: 5px;">Tanggal Produksi</label>
                    <input type="date" name="date" value="{{ $dateFilter }}" onchange="this.form.submit()" class="form-input" style="height: 38px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748B; margin-bottom: 5px;">Pilih Batch</label>
                    <select name="batch_id" onchange="this.form.submit()" class="form-select" style="height: 38px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; min-width: 220px;">
                        <option value="">Semua Batch Aktif</option>
                        @foreach ($activeBatchOptions as $bOpt)
                            <option value="{{ $bOpt->id }}" {{ $batchIdFilter == $bOpt->id ? 'selected' : '' }}>
                                {{ $bOpt->batch_code }} - {{ $bOpt->menu?->nama }} ({{ $bOpt->status_label }})
                            </option>
                        @endforeach
                    </select>
                </div>

                @if ($dateFilter || $batchIdFilter)
                    <div style="display: flex; align-items: flex-end;">
                        <a href="{{ route('staff.pengolahan.index') }}" class="btn-secondary" style="height: 38px; padding: 0 14px; font-size: 12.5px; display: inline-flex; align-items: center; border-radius: 8px; box-sizing: border-box;">
                            Reset Filter
                        </a>
                    </div>
                @endif
            </div>

            <div style="font-size: 12.5px; color: #64748B; padding-bottom: 8px;">
                Menampilkan <strong>{{ $batches->count() }}</strong> batch dalam antrean pengolahan
            </div>
        </form>
    </div>

    {{-- Daftar Kartu Batch Pengolahan --}}
    @forelse ($batches as $batch)
        @php
            $steps = $batch->processingSteps;
            $allDone = $steps->isNotEmpty() && $steps->every(fn ($s) => $s->status === 'done');
            $hasInProgress = $steps->contains(fn ($s) => $s->status === 'in_progress');
        @endphp
        <div class="batch-processing-card" id="batch_card_{{ $batch->id }}">
            {{-- Header Kartu Batch --}}
            <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding-bottom: 18px; border-bottom: 1px solid #F1F5F9;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <a href="{{ route('staff.batches.show', $batch->id) }}" style="font-size: 17px; font-weight: 700; color: #042A70; text-decoration: none;">
                            {{ $batch->batch_code }}
                        </a>
                        <span class="badge-status {{ $batch->status_badge_class }}">
                            <span class="status-dot"></span>
                            <span>{{ $batch->status_label }}</span>
                        </span>
                        <span class="chip-kategori">{{ $batch->menu?->kategori_label }}</span>
                        <span class="chip-porsi {{ $batch->recipe?->ukuran_porsi === 'besar' ? 'chip-porsi-besar' : 'chip-porsi-kecil' }}">
                            Porsi {{ ucfirst($batch->recipe?->ukuran_porsi) }}
                        </span>
                    </div>
                    <div style="font-size: 13.5px; color: #475569; margin-top: 4px;">
                        Menu: <strong>{{ $batch->menu?->nama }}</strong> • Target: <strong>{{ number_format($batch->target_porsi, 0, ',', '.') }} porsi</strong> • Tanggal: <strong>{{ $batch->production_date ? $batch->production_date->format('d M Y') : '-' }}</strong>
                    </div>
                </div>

                {{-- Action Tombol Tambah Tahap di Header Kartu --}}
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" 
                            onclick="openAddStepModal({{ $batch->id }}, '{{ $batch->batch_code }}', '{{ $batch->menu?->nama }}', '{{ $batch->menu?->kategori }}')" 
                            class="btn-secondary" 
                            style="font-size: 13px; font-weight: 600; padding: 7px 14px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Tahap</span>
                    </button>
                    <a href="{{ route('staff.batches.show', $batch->id) }}" class="btn-action-icon" title="Lihat Detail Batch">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </a>
                </div>
            </div>

            {{-- Linimasa Vertikal Tahap --}}
            @if ($steps->isEmpty())
                <div style="text-align: center; padding: 30px 20px; background: #F8FAFC; border-radius: 10px; margin-top: 18px;">
                    <div style="color: #64748B; font-size: 13px;">Belum ada tahap yang dicatat untuk batch ini.</div>
                    <div style="font-size: 12px; color: #94A3B8; margin-top: 4px;">Klik tombol "Tambah Tahap" di atas untuk memulai pencatatan alur pengolahan.</div>
                    <button type="button" 
                            onclick="openAddStepModal({{ $batch->id }}, '{{ $batch->batch_code }}', '{{ $batch->menu?->nama }}', '{{ $batch->menu?->kategori }}')" 
                            class="btn-primary" 
                            style="font-size: 12.5px; padding: 6px 14px; margin-top: 12px;">
                        Mulai Tahap Pertama
                    </button>
                </div>
            @else
                <div class="timeline-vertical" style="margin-top: 20px;">
                    @foreach ($steps as $step)
                        <div class="timeline-step-item">
                            <div class="timeline-step-node {{ $step->status }}">
                                @if ($step->status === 'done')
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                @endif
                            </div>
                            <div class="timeline-step-body">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-weight: 700; font-size: 14.5px; color: #071E49;">{{ $step->step_name }}</span>
                                        <span class="badge-status {{ $step->status_badge_class }}">
                                            <span class="status-dot"></span>
                                            <span>{{ $step->status_label }}</span>
                                        </span>
                                    </div>

                                    @if ($step->status === 'in_progress')
                                        <button type="button" 
                                                onclick="openCompleteStepModal({{ $step->id }}, '{{ $step->step_name }}', '{{ $step->started_at->format('Y-m-d\TH:i') }}')" 
                                                class="btn-success" 
                                                style="font-size: 12px; padding: 5px 12px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>Selesaikan Tahap</span>
                                        </button>
                                    @endif
                                </div>

                                <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
                                    Mulai: <strong>{{ $step->started_at ? $step->started_at->format('d/m/Y H:i') : '-' }}</strong>
                                    @if ($step->ended_at)
                                        • Selesai: <strong>{{ $step->ended_at->format('d/m/Y H:i') }}</strong>
                                        • Durasi: <strong style="color: #16A34A;">{{ $step->duration_human }}</strong>
                                    @else
                                        • <span style="color: #D97706; font-weight: 600;">(Sedang berlangsung...)</span>
                                    @endif
                                    • Petugas: <em>{{ $step->recorder?->nama ?? 'Staff' }}</em>
                                </div>

                                @if ($step->note)
                                    <div style="font-size: 12.5px; color: #334155; margin-top: 8px; background: #FFFFFF; padding: 8px 12px; border-radius: 6px; border: 1px solid #E2E8F0; white-space: pre-line;">
                                        {{ $step->note }}
                                    </div>
                                @endif

                                @if ($step->evidences->isNotEmpty())
                                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                                        @foreach ($step->evidences as $ev)
                                            <div onclick="previewEvidence('{{ asset('storage/' . $ev->file_path) }}', '{{ $step->step_name }}')" 
                                                 style="cursor: pointer; border-radius: 6px; overflow: hidden; border: 1px solid #CBD5E1; width: 60px; height: 46px;" 
                                                 title="Klik untuk memperbesar bukti">
                                                <img src="{{ asset('storage/' . $ev->file_path) }}" alt="Bukti" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Banner Jika Semua Tahap Selesai --}}
            @if ($allDone)
                <div style="margin-top: 20px; background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 14.5px; color: #166534;">Seluruh Tahapan Pengolahan Selesai</div>
                            <div style="font-size: 12.5px; color: #15803D;">Batch telah melalui seluruh alur kerja dan siap untuk pencatatan hasil porsi & uji kualitas.</div>
                        </div>
                    </div>
                    <button type="button" onclick="notifyHasil('{{ $batch->batch_code }}', {{ $batch->id }})" class="btn-success" style="padding: 8px 18px; font-size: 13px;">
                        <span>Catat Hasil Porsi</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </div>
            @endif
        </div>
    @empty
        {{-- Keadaan Kosong (Empty State) --}}
        <div class="table-card" style="text-align: center; padding: 60px 20px; color: #64748B;">
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: #F1F5F9; display: flex; align-items: center; justify-content: center; color: #94A3B8;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 14 14"></polyline>
                    </svg>
                </div>
                <div style="font-weight: 700; font-size: 16px; color: #071E49;">Belum Ada Batch yang Dikonfirmasi</div>
                <p style="font-size: 13.5px; color: #64748B; margin: 0; max-width: 440px; line-height: 1.5;">
                    Laman pengolahan menampilkan batch yang telah dikonfirmasi dan siap diolah di dapur. Silakan konfirmasi batch di laman Batch Produksi.
                </p>
                <a href="{{ route('staff.batches.index') }}" class="btn-primary" style="margin-top: 8px;">
                    Menuju Laman Batch Produksi
                </a>
            </div>
        </div>
    @endforelse
</div>

{{-- Modal Tambah Tahap --}}
<div id="modalAddStep" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #071E49;">Tambah Tahap Pengolahan</h3>
                <p style="margin: 2px 0 0; font-size: 12px; color: #64748B;" id="addStepSubtitle">Batch</p>
            </div>
            <button type="button" onclick="closeAddStepModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('staff.pengolahan.step.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="production_batch_id" id="addStepBatchId">

            <div class="modal-body-custom">
                {{-- Dropdown Batch (jika dibuka dari header utama) --}}
                <div id="batchSelectContainer" style="margin-bottom: 16px; display: none;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Pilih Batch Produksi <span style="color: #DC2626;">*</span>
                    </label>
                    <select id="modalBatchSelector" onchange="onModalBatchChange(this)" class="form-select" style="height: 42px; font-size: 13.5px; border-radius: 8px; border: 1px solid #CBD5E1; width: 100%; padding: 0 12px;">
                        <option value="">-- Pilih Batch --</option>
                        @foreach ($activeBatchOptions as $b)
                            <option value="{{ $b->id }}" data-code="{{ $b->batch_code }}" data-menu="{{ $b->menu?->nama }}" data-kategori="{{ $b->menu?->kategori }}">
                                {{ $b->batch_code }} - {{ $b->menu?->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Saran Tahap Sesuai Kategori --}}
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Nama Tahap Kerja <span style="color: #DC2626;">*</span>
                    </label>
                    <div style="margin-bottom: 8px;">
                        <span style="font-size: 11.5px; color: #64748B; display: block; margin-bottom: 4px;">Saran tahap (klik untuk memilih):</span>
                        <div id="stepSuggestionsPills" style="display: flex; flex-wrap: wrap; gap: 6px;">
                            {{-- Diisi secara dinamis via JS --}}
                        </div>
                    </div>
                    <input type="text" name="step_name" id="stepNameInput" required class="form-input" placeholder="Ketik nama tahap atau klik saran di atas..." style="height: 42px; font-size: 13.5px; border-radius: 8px; border: 1px solid #CBD5E1; width: 100%; padding: 0 12px;">
                </div>

                {{-- Waktu Mulai --}}
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Waktu Mulai <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="datetime-local" name="started_at" id="startedAtInput" required class="form-input" style="height: 42px; font-size: 13.5px; border-radius: 8px; border: 1px solid #CBD5E1; width: 100%; padding: 0 12px;">
                </div>

                {{-- Catatan (Opsional) --}}
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Catatan Pengerjaan (Opsional)
                    </label>
                    <textarea name="note" rows="2" class="form-input" placeholder="Contoh: Suhu air 100°C, menggunakan kuali no. 2..." style="width: 100%; font-size: 13px; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1;"></textarea>
                </div>

                {{-- Upload Bukti Foto (Opsional) --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Foto Bukti Aktivitas (Opsional)
                    </label>
                    <input type="file" name="evidence_file" accept="image/*" class="form-input" style="width: 100%; font-size: 13px; padding: 8px; border-radius: 8px; border: 1px solid #CBD5E1;">
                    <small style="color: #64748B; font-size: 11px; margin-top: 3px; display: block;">Format: JPG, PNG, WEBP. Maks 10 MB.</small>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" onclick="closeAddStepModal()" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Mulai Tahap Ini</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Selesaikan Tahap --}}
<div id="modalCompleteStep" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #071E49;">Selesaikan Tahap Kerja</h3>
                <p style="margin: 2px 0 0; font-size: 12px; color: #64748B;" id="completeStepSubtitle">Nama Tahap</p>
            </div>
            <button type="button" onclick="closeCompleteStepModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form id="formCompleteStep" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="modal-body-custom">
                {{-- Waktu Selesai --}}
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Waktu Selesai <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="datetime-local" name="ended_at" id="endedAtInput" required class="form-input" style="height: 42px; font-size: 13.5px; border-radius: 8px; border: 1px solid #CBD5E1; width: 100%; padding: 0 12px;">
                    <small id="minStartedNote" style="color: #64748B; font-size: 11.5px; margin-top: 3px; display: block;"></small>
                </div>

                {{-- Catatan Tambahan --}}
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Catatan Hasil / Selesai (Opsional)
                    </label>
                    <textarea name="note" rows="2" class="form-input" placeholder="Contoh: Tahap selesai tepat waktu, tingkat kematangan pas..." style="width: 100%; font-size: 13px; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1;"></textarea>
                </div>

                {{-- Upload Bukti Selesai --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Foto Bukti Selesai (Opsional)
                    </label>
                    <input type="file" name="evidence_file" accept="image/*" class="form-input" style="width: 100%; font-size: 13px; padding: 8px; border-radius: 8px; border: 1px solid #CBD5E1;">
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" onclick="closeCompleteStepModal()" class="btn-secondary">Kembali</button>
                <button type="submit" class="btn-success">Simpan & Tandai Selesai</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Preview Foto Bukti --}}
<div id="modalPreviewEvidence" class="modal-backdrop-custom" onclick="closePreviewEvidence()">
    <div style="max-width: 90vw; max-height: 90vh; position: relative;">
        <img id="previewEvidenceImg" src="" alt="Bukti Foto" style="max-width: 100%; max-height: 85vh; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
        <div id="previewEvidenceCaption" style="color: #FFFFFF; text-align: center; margin-top: 10px; font-weight: 600; font-size: 14px;"></div>
    </div>
</div>

@push('scripts')
<script>
    const categorySuggestions = {
        'makanan_siap_santap': ['Persiapan bahan', 'Pencucian', 'Pemasakan', 'Pengemasan'],
        'paket_siap_konsumsi': ['Pemeriksaan produk', 'Pengemasan paket'],
        'pangan_siap_olah': ['Persiapan bahan', 'Pengolahan awal', 'Pengemasan'],
    };
    const defaultSuggestions = ['Persiapan bahan', 'Pencucian', 'Pemasakan', 'Pengemasan'];

    function formatDateTimeLocal(d) {
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function openAddStepModal(batchId = null, batchCode = '', menuName = '', kategori = '') {
        const batchIdInput = document.getElementById('addStepBatchId');
        const batchSelectDiv = document.getElementById('batchSelectContainer');
        const subtitle = document.getElementById('addStepSubtitle');
        const startedInput = document.getElementById('startedAtInput');

        startedInput.value = formatDateTimeLocal(new Date());

        if (batchId) {
            batchIdInput.value = batchId;
            batchSelectDiv.style.display = 'none';
            subtitle.textContent = `Batch: ${batchCode} • ${menuName}`;
            populateSuggestions(kategori);
        } else {
            batchIdInput.value = '';
            batchSelectDiv.style.display = 'block';
            subtitle.textContent = 'Pilih batch yang akan ditambahkan tahapan kerja';
            populateSuggestions('');
        }

        document.getElementById('stepNameInput').value = '';
        document.getElementById('modalAddStep').classList.add('show');
    }

    function onModalBatchChange(select) {
        const opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) return;

        document.getElementById('addStepBatchId').value = opt.value;
        const kategori = opt.getAttribute('data-kategori') || '';
        populateSuggestions(kategori);
    }

    function populateSuggestions(kategori) {
        const container = document.getElementById('stepSuggestionsPills');
        container.innerHTML = '';

        const list = categorySuggestions[kategori] || defaultSuggestions;
        list.forEach(item => {
            const pill = document.createElement('button');
            pill.type = 'button';
            pill.style.cssText = 'padding: 3px 10px; border-radius: 9999px; background: #EFF6FF; color: #1D4ED8; font-size: 11.5px; font-weight: 600; border: 1px solid #DBEAFE; cursor: pointer; transition: all 0.15s ease;';
            pill.textContent = item;
            pill.onclick = () => {
                document.getElementById('stepNameInput').value = item;
            };
            container.appendChild(pill);
        });
    }

    function closeAddStepModal() {
        document.getElementById('modalAddStep').classList.remove('show');
    }

    function openCompleteStepModal(stepId, stepName, startedAtStr) {
        const form = document.getElementById('formCompleteStep');
        form.action = `/staff/pengolahan/tahap/${stepId}/selesaikan`;

        document.getElementById('completeStepSubtitle').textContent = `Tahap: ${stepName}`;
        const endedInput = document.getElementById('endedAtInput');
        endedInput.value = formatDateTimeLocal(new Date());
        endedInput.min = startedAtStr;

        document.getElementById('minStartedNote').textContent = `Waktu mulai: ${startedAtStr.replace('T', ' ')}. Waktu selesai tidak boleh lebih awal.`;
        document.getElementById('modalCompleteStep').classList.add('show');
    }

    function closeCompleteStepModal() {
        document.getElementById('modalCompleteStep').classList.remove('show');
    }

    function previewEvidence(url, caption) {
        const modal = document.getElementById('modalPreviewEvidence');
        document.getElementById('previewEvidenceImg').src = url;
        document.getElementById('previewEvidenceCaption').textContent = caption;
        modal.classList.add('show');
    }

    function closePreviewEvidence() {
        document.getElementById('modalPreviewEvidence').classList.remove('show');
    }

    function notifyHasil(batchCode, batchId) {
        alert(`Batch ${batchCode} siap dicatat hasilnya!\n\nSeluruh tahapan pengolahan telah selesai. Alur akan diteruskan ke modul Hasil Produksi (Pre-Distribution Check).`);
    }
</script>
@endpush
@endsection
