@extends('layouts.staff')

@section('title', 'Distribusi - Pengiriman')

@section('content')
<div class="pengiriman-container">
    {{-- Header Judul & Subtitle --}}
    <div class="pengiriman-header">
        <h1 class="pengiriman-title">Distribusi & Pengiriman</h1>
        <p class="pengiriman-subtitle">
            Kelola alokasi pengiriman porsi makanan dari SPPG ke sekolah/posyandu tujuan dan pantau status keberangkatan armada.
        </p>
    </div>

    {{-- Notifikasi Flash Message --}}
    @if (session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center justify-between alert-auto-dismiss">
            <div class="flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.closest('.alert-auto-dismiss').remove()" class="text-emerald-600 hover:text-emerald-900">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-medium flex items-center justify-between alert-auto-dismiss">
            <div class="flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.closest('.alert-auto-dismiss').remove()" class="text-red-600 hover:text-red-900">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
            <div class="font-semibold mb-1">Terjadi kesalahan validasi:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 3 KPI Statistik Cards --}}
    <div class="pengiriman-stats-grid">
        {{-- Card 1: Total Pengiriman --}}
        <div class="pengiriman-stat-card">
            <div class="pengiriman-icon-box icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
            </div>
            <div class="pengiriman-stat-content">
                <span class="pengiriman-stat-label">Total Pengiriman</span>
                <span class="pengiriman-stat-value">{{ number_format($stats['total_pengiriman'], 0, ',', '.') }}</span>
                <span class="pengiriman-stat-sub">Semua transaksi tercatat</span>
            </div>
        </div>

        {{-- Card 2: Pengiriman Berjalan (Dalam Proses / Kirim) --}}
        <div class="pengiriman-stat-card">
            <div class="pengiriman-icon-box icon-amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="pengiriman-stat-content">
                <span class="pengiriman-stat-label">Dalam Proses / Kirim</span>
                <span class="pengiriman-stat-value">{{ number_format($stats['dalam_proses'], 0, ',', '.') }}</span>
                <span class="pengiriman-stat-sub">Direncanakan & armada jalan</span>
            </div>
        </div>

        {{-- Card 3: Porsi Sukses Diterima --}}
        <div class="pengiriman-stat-card">
            <div class="pengiriman-icon-box icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="pengiriman-stat-content">
                <span class="pengiriman-stat-label">Porsi Sukses Diterima</span>
                <span class="pengiriman-stat-value">{{ number_format($stats['total_porsi_terima'], 0, ',', '.') }}</span>
                <span class="pengiriman-stat-sub">Porsi sampai di lokasi tujuan</span>
            </div>
        </div>
    </div>

    {{-- Tabel & Filter Card --}}
    <div class="pengiriman-table-card">
        {{-- Filter Bar Form --}}
        <form action="{{ route('staff.pengiriman.index') }}" method="GET" class="pengiriman-filter-bar">
            {{-- Search Box --}}
            <div class="pengiriman-search-box">
                <svg class="pengiriman-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" class="pengiriman-search-input" placeholder="Cari batch, menu, atau tujuan..." onchange="this.form.submit()">
            </div>

            {{-- Actions Right --}}
            <div class="pengiriman-actions-right">
                {{-- Dropdown Tipe Filter --}}
                <div class="pengiriman-select-wrapper">
                    <select name="tipe" class="pengiriman-select" onchange="this.form.submit()">
                        <option value="" {{ in_array(strtolower($tipe), ['', 'semua', 'semua tipe'], true) ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="sekolah" {{ strtolower($tipe) === 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                        <option value="posyandu" {{ strtolower($tipe) === 'posyandu' ? 'selected' : '' }}>Posyandu</option>
                        <option value="lainnya" {{ strtolower($tipe) === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <svg class="pengiriman-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Status Filter --}}
                <div class="pengiriman-select-wrapper">
                    <select name="status" class="pengiriman-select" onchange="this.form.submit()">
                        <option value="" {{ $status === '' ? 'selected' : '' }}>Semua Status</option>
                        <option value="planned" {{ $status === 'planned' ? 'selected' : '' }}>Direncanakan</option>
                        <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Dalam Kirim</option>
                        <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Diterima</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                    <svg class="pengiriman-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Filter Tanggal --}}
                <input type="date" name="date" value="{{ $date }}" class="pengiriman-date-input" onchange="this.form.submit()" title="Filter berdasarkan tanggal pengiriman">

                {{-- Reset Filter Button --}}
                @if ($q || $tipe || $status || $destinationId || $date)
                    <a href="{{ route('staff.pengiriman.index') }}" class="btn-pengiriman-reset" title="Reset semua filter">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif

                {{-- Tombol Tambah / Catat Pengiriman --}}
                <button type="button" class="btn-pengiriman-primary" onclick="openModalCatat()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Catat Pengiriman</span>
                </button>
            </div>
        </form>

        {{-- Tabel Pengiriman --}}
        <div class="overflow-x-auto">
            <table class="pengiriman-table">
                <thead>
                    <tr>
                        <th>Kode Batch & Menu</th>
                        <th>Tujuan Distribusi</th>
                        <th>Porsi Dikirim</th>
                        <th>Status</th>
                        <th>Waktu Berangkat</th>
                        <th>Petugas</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($distributions as $dist)
                        <tr>
                            {{-- Kode Batch & Menu --}}
                            <td>
                                <div class="font-bold text-[#071E49]">
                                    {{ $dist->batch?->batch_code ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $dist->batch?->menu?->nama ?? '-' }}
                                </div>
                            </td>

                            {{-- Tujuan Distribusi --}}
                            <td>
                                <div class="font-semibold text-slate-800">
                                    {{ $dist->destination?->nama ?? '-' }}
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-500">
                                    <span class="inline-block px-1.5 py-0.2 text-[10px] bg-slate-100 rounded text-slate-600 font-medium">
                                        {{ $dist->destination?->tipe_label ?? 'Tujuan' }}
                                    </span>
                                    <span>Target: {{ number_format($dist->destination?->jumlah_penerima ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </td>

                            {{-- Porsi Dikirim --}}
                            <td>
                                <span class="font-bold text-slate-800">
                                    {{ number_format($dist->portions_sent, 0, ',', '.') }}
                                </span>
                                <span class="text-xs text-slate-500">porsi</span>

                                @if ($dist->is_over_quota)
                                    <span class="ml-1 inline-flex items-center text-[11px] text-amber-600 font-medium" title="Porsi melebihi target penerima standar tujuan">
                                        (Kelebihan)
                                    </span>
                                @endif
                            </td>

                            {{-- Status Chip --}}
                            <td>
                                <span class="status-chip {{ $dist->status_badge_class }}">
                                    {{ $dist->status_label }}
                                </span>
                            </td>

                            {{-- Waktu Berangkat --}}
                            <td>
                                <div class="text-sm text-slate-700">
                                    {{ $dist->distributed_at ? $dist->distributed_at->translatedFormat('d M Y') : '-' }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $dist->distributed_at ? $dist->distributed_at->format('H:i') . ' WIB' : '' }}
                                </div>
                            </td>

                            {{-- Petugas Pencatat --}}
                            <td>
                                <span class="text-sm text-slate-600">
                                    {{ $dist->recorder?->nama ?? 'Staff' }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td>
                                <div class="pengiriman-actions-cell justify-end">
                                    {{-- Tombol Detail (Buka Slide-over Drawer) --}}
                                    <button type="button" class="btn-act btn-act-detail" onclick="openDrawerDetail({{ $dist->id }})" title="Lihat detail lengkap">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <span>Detail</span>
                                    </button>

                                    @if ($dist->status === 'planned')
                                        {{-- Tombol Kirim / Berangkatkan --}}
                                        <button type="button" class="btn-act btn-act-send" onclick="openModalSent({{ $dist->id }}, @js($dist->destination?->nama ?? '-'), @js($dist->batch?->batch_code ?? '-'), {{ $dist->portions_sent }})" title="Memberangkatkan pengiriman">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                            </svg>
                                            <span>Kirim</span>
                                        </button>

                                        {{-- Tombol Ubah --}}
                                        @php
                                            $allocatedOthers = $dist->batch ? (int) $dist->batch->distributions()->where('id', '!=', $dist->id)->where('status', '!=', 'cancelled')->sum('portions_sent') : 0;
                                            $maxAvail = $dist->batch ? max(0, (int) $dist->batch->actual_porsi - $allocatedOthers) : $dist->portions_sent;
                                            $distAtRaw = $dist->distributed_at ? $dist->distributed_at->format('Y-m-d\TH:i') : '';
                                        @endphp
                                        <button type="button" class="btn-act btn-act-detail" onclick="openModalEdit({{ $dist->id }}, {{ $dist->destination_id }}, {{ $dist->portions_sent }}, @js($distAtRaw), @js($dist->batch?->batch_code ?? '-'), {{ $maxAvail }})" title="Ubah data pengiriman">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            <span>Ubah</span>
                                        </button>

                                        {{-- Tombol Batal --}}
                                        <button type="button" class="btn-act btn-act-cancel" onclick="openModalCancel({{ $dist->id }}, @js($dist->destination?->nama ?? '-'), @js($dist->batch?->batch_code ?? '-'))" title="Batalkan rencana pengiriman">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                            </svg>
                                            <span>Batal</span>
                                        </button>
                                    @elseif ($dist->status === 'sent')
                                        {{-- Tombol Konfirmasi Diterima --}}
                                        <button type="button" class="btn-act btn-act-receive" onclick="openModalReceived({{ $dist->id }}, @js($dist->destination?->nama ?? '-'), @js($dist->batch?->batch_code ?? '-'), {{ $dist->portions_sent }})" title="Tandai telah sampai dan diterima">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            <span>Terima</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="pengiriman-empty-state">
                                    <div class="pengiriman-empty-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <rect x="1" y="3" width="15" height="13"></rect>
                                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                        </svg>
                                    </div>
                                    <h4 class="pengiriman-empty-title">Belum Ada Catatan Pengiriman</h4>
                                    <p class="pengiriman-empty-desc">
                                        Data alokasi dan pengiriman makanan akan muncul di sini setelah Anda mencatat pengiriman dari batch yang siap kirim.
                                    </p>
                                    <button type="button" class="btn-primary" onclick="openModalCatat()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>Catat Pengiriman Sekarang</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($distributions->hasPages())
            <div class="pengiriman-pagination-wrapper">
                <div class="text-xs text-slate-500">
                    Menampilkan <b>{{ $distributions->firstItem() ?? 0 }}</b> - <b>{{ $distributions->lastItem() ?? 0 }}</b> dari <b>{{ $distributions->total() }}</b> pengiriman
                </div>
                <div>
                    {{ $distributions->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modals & Slide-over Drawer --}}
@include('staff.pengiriman.partials.modals')
@endsection
