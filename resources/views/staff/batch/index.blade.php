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
        <a href="{{ route('staff.batches.create') }}" class="btn-primary" style="padding: 10px 20px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Buat Batch Baru</span>
        </a>
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
        <form method="GET" action="{{ route('staff.batches.index') }}" id="filterForm" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; flex-wrap: wrap;">
                {{-- Search Box --}}
                <div style="position: relative; min-width: 220px; flex: 1;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94A3B8;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode batch, menu..." class="form-input" style="padding-left: 38px; height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; width: 100%;">
                </div>

                {{-- Status Filter --}}
                <div style="min-width: 150px;">
                    <select name="status" onchange="this.form.submit()" class="form-select" style="height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; width: 100%;">
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
                <div style="min-width: 170px;">
                    <select name="menu_id" onchange="this.form.submit()" class="form-select" style="height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; width: 100%;">
                        <option value="">Semua Menu</option>
                        @foreach ($menus as $m)
                            <option value="{{ $m->id }}" {{ $menuId == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori Filter --}}
                <div style="min-width: 170px;">
                    <select name="kategori" onchange="this.form.submit()" class="form-select" style="height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; width: 100%;">
                        <option value="">Semua Kategori</option>
                        <option value="makanan_siap_santap" {{ $kategori === 'makanan_siap_santap' ? 'selected' : '' }}>Makanan Siap Santap</option>
                        <option value="paket_siap_konsumsi" {{ $kategori === 'paket_siap_konsumsi' ? 'selected' : '' }}>Paket Siap Konsumsi</option>
                        <option value="pangan_siap_olah" {{ $kategori === 'pangan_siap_olah' ? 'selected' : '' }}>Pangan Siap Olah</option>
                    </select>
                </div>

                {{-- Date From & To --}}
                <div style="display: flex; align-items: center; gap: 6px;">
                    <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="this.form.submit()" title="Dari Tanggal" class="form-input" style="height: 40px; font-size: 12.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px;">
                    <span style="color: #94A3B8; font-size: 12px;">s/d</span>
                    <input type="date" name="date_to" value="{{ $dateTo }}" onchange="this.form.submit()" title="Sampai Tanggal" class="form-input" style="height: 40px; font-size: 12.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px;">
                </div>

                @if($q || $status || $menuId || $kategori || $dateFrom || $dateTo)
                    <a href="{{ route('staff.batches.index') }}" class="btn-secondary" style="height: 40px; padding: 0 12px; font-size: 12.5px; display: inline-flex; align-items: center;">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Table Content --}}
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width: 140px;">Kode Batch</th>
                        <th style="min-width: 110px;">Tgl Produksi</th>
                        <th style="min-width: 170px;">Menu</th>
                        <th style="min-width: 140px;">Kategori</th>
                        <th style="min-width: 90px;">Ukuran</th>
                        <th style="min-width: 80px; text-align: center;">Target</th>
                        <th style="min-width: 80px; text-align: center;">Hasil</th>
                        <th style="min-width: 120px;">Status</th>
                        <th style="min-width: 120px; text-align: right;">Biaya / Porsi</th>
                        <th style="min-width: 110px;">Pembuat</th>
                        <th style="min-width: 120px; text-align: center;">Aksi</th>
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
                                <span class="chip-kategori">
                                    {{ $batch->menu?->kategori_label ?? '-' }}
                                </span>
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
                            <td style="color: #64748B; font-size: 12.5px;">
                                {{ $batch->creator?->nama ?? 'Staff' }}
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
                                        {{-- Ubah (hanya draft) --}}
                                        <a href="{{ route('staff.batches.edit', $batch->id) }}" class="btn-action-icon" title="Ubah Batch Draft">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>

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
                            <td colspan="11" style="text-align: center; padding: 40px 20px; color: #64748B;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;">
                                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #F1F5F9; display: flex; align-items: center; justify-content: center; color: #94A3B8;">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                                            <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                                            <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                                            <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                        </svg>
                                    </div>
                                    <div style="font-weight: 600; font-size: 15px; color: #071E49;">Belum Ada Data Batch Produksi</div>
                                    <p style="font-size: 13px; color: #64748B; margin: 0; max-width: 400px;">
                                        Silakan buat batch produksi baru dengan memilih menu aktif dan mengalokasikan lot bahan baku.
                                    </p>
                                    <a href="{{ route('staff.batches.create') }}" class="btn-primary" style="margin-top: 8px;">
                                        Buat Batch Baru
                                    </a>
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

{{-- Modal Konfirmasi Batch --}}
<div id="modalConfirmBatch" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #071E49;">Konfirmasi Batch Produksi</h3>
                    <p style="margin: 2px 0 0; font-size: 12px; color: #64748B;" id="confirmBatchSubtitle">Kode Batch</p>
                </div>
            </div>
            <button type="button" onclick="closeConfirmModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form id="formConfirmBatch" method="POST" action="">
            @csrf
            <div class="modal-body-custom">
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; margin-bottom: 16px;">
                    <div style="font-size: 13px; color: #334155; line-height: 1.5;">
                        Dengan mengonfirmasi batch ini:
                        <ul style="margin: 8px 0 0 18px; padding: 0; color: #475569;">
                            <li>Stok fisik bahan pada masing-masing lot terpilih akan <strong>langsung dipotong</strong> (mutasi <em>PRODUCTION_OUT</em>).</li>
                            <li>Harga satuan lot saat ini akan dicatat permanen (<em>snapshot unit cost</em>).</li>
                            <li>Dokumen batch akan <strong>terkunci</strong> (<em>is_locked = true</em>) dan siap masuk ke tahap pengolahan.</li>
                        </ul>
                    </div>
                </div>
                <div class="alert-warning-pill" style="margin-top: 0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>Pastikan alokasi lot bahan telah memenuhi kebutuhan porsi sebelum melanjutkan.</span>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" onclick="closeConfirmModal()" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-success">Konfirmasi & Potong Stok</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Batalkan Batch --}}
<div id="modalCancelBatch" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #071E49;">Batalkan Batch Produksi</h3>
                    <p style="margin: 2px 0 0; font-size: 12px; color: #64748B;" id="cancelBatchSubtitle">Draft Batch</p>
                </div>
            </div>
            <button type="button" onclick="closeCancelModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form id="formCancelBatch" method="POST" action="">
            @csrf
            <div class="modal-body-custom">
                <p style="font-size: 13.5px; color: #475569; margin: 0 0 14px;">
                    Apakah Anda yakin ingin membatalkan draft batch produksi ini? Batch yang dibatalkan tidak akan dapat diproses lagi.
                </p>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Alasan Pembatalan <span style="color: #DC2626;">*</span>
                    </label>
                    <textarea name="reason" required rows="3" class="form-input" placeholder="Contoh: Perubahan menu sekolah, kesalahan input target porsi..." style="width: 100%; font-size: 13px; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1;"></textarea>
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
