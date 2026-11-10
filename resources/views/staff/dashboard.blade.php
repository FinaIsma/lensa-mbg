@extends('layouts.staff')

@section('title', 'Dashboard Operasional - Staff SPPG')

@push('styles')
    @vite(['resources/css/staff/dashboard.css'])
@endpush

@section('content')
<div class="space-y-7 pb-8">
    {{-- Header Area: Sapaan & Pill SPPG --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 page-title-section">
        <div>
            <h1 class="page-title">
                Selamat datang, {{ $user->nama ?? 'Staff' }} di Dashboard Operasional SPPG
            </h1>
            <p class="page-subtitle">
                Ringkasan data dan aktivitas operasional SPPG secara real-time.
            </p>
        </div>

        <div class="header-badge-pill self-start md:self-auto">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span class="font-medium text-slate-600">{{ $todayStr }}</span>
            <span class="text-slate-300">•</span>
            <span class="font-bold text-[#071E49]">{{ $sppg->nama_sppg ?? 'SPPG MBG' }}</span>
        </div>
    </div>

    {{-- BARIS 1: 4 STAT CARDS GRID (TERHUBUNG KE HALAMAN MASING-MASING) --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Porsi Siap / Target (Menghubungkan ke Modul Batch Produksi) --}}
        <a href="{{ route('staff.batches.index') }}" class="stat-card" title="Buka Modul Produksi Batch">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Porsi Siap / Target</span>
                <span class="stat-value">{{ number_format($stats['porsi_siap'], 0, ',', '.') }} / {{ number_format($stats['porsi_target'], 0, ',', '.') }}</span>
                <span class="stat-subtext">{{ $stats['batch_count'] }} Batch Terjadwal &rarr;</span>
            </div>
        </a>

        {{-- Card 2: Porsi Terdistribusi (Menghubungkan ke Modul Pengiriman) --}}
        <a href="{{ route('staff.pengiriman.index') }}" class="stat-card" title="Buka Modul Pengiriman">
            <div class="stat-icon-wrapper icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Porsi Terdistribusi</span>
                <span class="stat-value">{{ number_format($stats['porsi_terdistribusi'], 0, ',', '.') }} Porsi</span>
                <span class="stat-subtext">Capaian {{ $stats['distribusi_pct'] }}% &rarr;</span>
            </div>
        </a>

        {{-- Card 3: Tugas Pending Operasional (Menghubungkan ke Modul Batch/PO) --}}
        <a href="{{ route('staff.batches.index') }}" class="stat-card" title="Tinjau Tugas Pending">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Tugas Pending</span>
                <span class="stat-value">{{ number_format($stats['pending_tasks_count'], 0, ',', '.') }} Tugas</span>
                <span class="stat-subtext">{{ $stats['batch_pending_count'] }} Batch &amp; {{ $stats['po_draft_count'] }} PO Aktif &rarr;</span>
            </div>
        </a>

        {{-- Card 4: Bahan Kritis Menipis (Menghubungkan ke Modul Bahan) --}}
        <a href="{{ route('staff.materials.index') }}" class="stat-card" title="Buka Master Bahan">
            <div class="stat-icon-wrapper icon-red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Bahan Kritis Menipis</span>
                <span class="stat-value">{{ number_format($stats['critical_materials_count'], 0, ',', '.') }} Bahan</span>
                <span class="stat-subtext">Periksa Stok Minimum &rarr;</span>
            </div>
        </a>
    </div>

    {{-- BARIS 2: VISUALISASI UTAMA (2 KOLOM SEIMBANG) --}}
    <div class="visual-cards-grid">
        {{-- Kiri: Capaian Distribusi per Sasaran (Progress Bars Real) --}}
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h2 class="dashboard-card-title">Capaian Distribusi per Sasaran</h2>
                    <p class="dashboard-card-subtitle">Realisasi alokasi porsi makanan bergizi berdasarkan kategori tujuan</p>
                </div>
                <a href="{{ route('staff.pengiriman.index') }}" class="btn-detail-link">
                    <span>Modul Pengiriman</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>

            <div class="dist-target-list">
                @foreach ($targetProgress as $tp)
                    <a href="{{ route('staff.pengiriman.index', ['tipe' => $tp['type']]) }}" class="dist-target-item block no-underline" title="Filter pengiriman tipe {{ $tp['label'] }}">
                        <div class="dist-target-head">
                            <span class="dist-target-name">
                                <span>{{ $tp['icon'] }}</span>
                                <span>{{ $tp['label'] }}</span>
                            </span>
                            <span class="dist-target-pct">{{ $tp['pct'] }}%</span>
                        </div>
                        <div class="dist-progress-track">
                            <div class="dist-progress-fill {{ $tp['fill_class'] }}" style="width: {{ $tp['pct'] }}%;"></div>
                        </div>
                        <div class="dist-target-meta">
                            <span>Terkirim: <strong>{{ number_format($tp['sent'], 0, ',', '.') }}</strong> dari {{ number_format($tp['target'], 0, ',', '.') }} porsi</span>
                            <span>{{ $tp['target'] > 0 ? ($tp['pct'] >= 100 ? '✅ Tuntas' : '🚚 Berjalan') : 'Belum Ada Alokasi' }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Kanan: Donut Chart Realisasi Porsi Hari Ini --}}
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h2 class="dashboard-card-title">Realisasi Porsi Hari Ini</h2>
                    <p class="dashboard-card-subtitle">Proporsi status distribusi makanan bergizi</p>
                </div>
            </div>

            <div class="radial-donut-wrapper">
                <div style="height: 190px; width: 100%; position: relative;">
                    <canvas id="porsiDonutChart"></canvas>
                    <div class="chart-center-badge">
                        <div class="chart-center-pct">{{ $donutData['completion_pct'] }}%</div>
                        <div class="chart-center-label">TERKIRIM</div>
                    </div>
                </div>

                <div class="donut-legend-grid">
                    <div class="donut-legend-item">
                        <span class="legend-dot dot-sent"></span>
                        <span class="text-slate-600">Diterima: <strong class="text-slate-800">{{ number_format($donutData['received'], 0, ',', '.') }}</strong></span>
                    </div>
                    <div class="donut-legend-item">
                        <span class="legend-dot dot-transit"></span>
                        <span class="text-slate-600">Dalam Kirim: <strong class="text-slate-800">{{ number_format($donutData['in_transit'], 0, ',', '.') }}</strong></span>
                    </div>
                    <div class="donut-legend-item">
                        <span class="legend-dot dot-planned"></span>
                        <span class="text-slate-600">Rencana: <strong class="text-slate-800">{{ number_format($donutData['planned'], 0, ',', '.') }}</strong></span>
                    </div>
                    <div class="donut-legend-item">
                        <span class="legend-dot dot-cancelled"></span>
                        <span class="text-slate-600">Batal: <strong class="text-slate-800">{{ number_format($donutData['cancelled'], 0, ',', '.') }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS 3: LIVE PIPELINE (STEPPER ALUR DAPUR - TIAP STEP BISA DIKLIK KE MODULNYA) --}}
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div>
                <h2 class="dashboard-card-title">Monitoring Alur Operasional Dapur Hari Ini</h2>
                <p class="dashboard-card-subtitle">Tahapan siklus produksi mulai dari pengadaan bahan hingga distribusi porsi</p>
            </div>
            @if ($latestBatch)
                <a href="{{ route('staff.batches.show', $latestBatch->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors no-underline">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                    <span>Batch #{{ $latestBatch->batch_code }} ({{ $latestBatch->menu?->nama ?? 'Menu Harian' }}) &rarr;</span>
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                    Alur Standar SPPG
                </span>
            @endif
        </div>

        <div class="process-flow-container">
            <div class="process-flow-track"></div>
            <div class="process-flow-steps">
                @foreach ($pipelineSteps as $step)
                    <a href="{{ $step['route'] }}" class="process-step-item {{ $step['state'] }}" title="Buka Modul {{ $step['title'] }}">
                        <div class="step-node-bubble">
                            @if ($step['state'] === 'completed')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @else
                                {{ $step['number'] }}
                            @endif
                        </div>
                        <span class="step-title">{{ $step['title'] }}</span>
                        <span class="step-desc">{{ $step['desc'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- BARIS 4: ANTREAN AKSI & OPERASIONAL HARIAN (REAL DATABASE) --}}
    <div class="operational-dual-grid">
        {{-- Kiri: Antrean Pengiriman Terbaru --}}
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h2 class="dashboard-card-title">Antrean Pengiriman Terbaru</h2>
                    <p class="dashboard-card-subtitle">Pengiriman porsi ke lokasi sekolah &amp; posyandu</p>
                </div>
                <a href="{{ route('staff.pengiriman.index') }}" class="btn-detail-link">
                    <span>Lihat Semua</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Batch &amp; Menu</th>
                            <th>Tujuan Sasaran</th>
                            <th class="text-center">Porsi</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentDistributions as $dist)
                            <tr>
                                <td>
                                    <div class="font-bold text-[#071E49]">{{ $dist->batch?->batch_code ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $dist->batch?->menu?->nama ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-800">{{ $dist->destination?->nama ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400 capitalize">{{ $dist->destination?->tipe_label ?? 'Tujuan' }}</div>
                                </td>
                                <td class="text-center font-bold text-[#071E49]">
                                    {{ number_format($dist->portions_sent, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="status-chip {{ $dist->status_badge_class }}">
                                        {{ $dist->status_label }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('staff.pengiriman.index') }}" class="btn-detail-link py-1 px-2.5 text-[11px]" title="Buka Detail di Modul Pengiriman">
                                        Buka
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    Belum ada transaksi pengiriman tercatat hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kanan: Peringatan Stok & Logistik Masuk --}}
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h2 class="dashboard-card-title">Peringatan Stok &amp; Logistik Masuk</h2>
                    <p class="dashboard-card-subtitle">Bahan di bawah batas minimum dan pengadaan barang</p>
                </div>
                <a href="{{ route('staff.purchases.create') }}" class="btn-primary-navy">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Buat PO</span>
                </a>
            </div>

            {{-- Bahan Kritis --}}
            <div class="mb-4">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                    Bahan Baku Menipis / Kritis ({{ $criticalMaterials->count() }})
                </div>
                @forelse ($criticalMaterials->take(4) as $mat)
                    <div class="material-critical-item">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                    <line x1="12" y1="9" x2="12" y2="13"></line>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-xs">{{ $mat->nama }} ({{ $mat->kode }})</div>
                                <div class="text-[11px] text-red-600 font-medium">
                                    Sisa: {{ number_format($mat->total_qty, 1, ',', '.') }} {{ $mat->satuan }} (Min: {{ number_format($mat->min_stock, 1, ',', '.') }} {{ $mat->satuan }})
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('staff.purchases.create') }}" class="btn-detail-link py-1 px-2.5 text-[11px]" title="Buat Pengadaan Bahan">
                            Restock
                        </a>
                    </div>
                @empty
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2 text-xs text-emerald-700">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Seluruh stok bahan baku saat ini berada di atas batas minimum.</span>
                    </div>
                @endforelse
            </div>

            {{-- PO Menunggu Barang Masuk / Riwayat PO --}}
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                    PO Terkini &amp; Pengadaan Barang ({{ $waitingPurchases->count() }})
                </div>
                @forelse ($waitingPurchases as $po)
                    <a href="{{ route('staff.purchases.index') }}" class="flex items-center justify-between p-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-100 rounded-lg mb-2 transition-colors no-underline block" title="Buka Modul Pembelian">
                        <div>
                            <div class="font-bold text-[#071E49] text-xs">{{ $po->purchase_no }}</div>
                            <div class="text-[11px] text-slate-500">{{ $po->supplier?->nama ?? 'Pemasok' }} • {{ $po->purchase_date ? \Carbon\Carbon::parse($po->purchase_date)->format('d M Y') : '-' }}</div>
                        </div>
                        @php
                            $badgeCls = match($po->status) {
                                'received' => 'badge-received',
                                'ordered', 'received_partial' => 'badge-sent',
                                'cancelled' => 'badge-cancelled',
                                default => 'badge-planned',
                            };
                            $labelCls = match($po->status) {
                                'received' => 'Diterima',
                                'ordered' => 'Dipesan',
                                'received_partial' => 'Parsial',
                                'cancelled' => 'Batal',
                                default => 'Draft',
                            };
                        @endphp
                        <span class="status-chip {{ $badgeCls }} text-[10px]">
                            {{ $labelCls }}
                        </span>
                    </a>
                @empty
                    <div class="text-xs text-slate-400 py-2">
                        Tidak ada transaksi PO tercatat.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.staffDashboardChartConfig = {
            donutData: @js($donutData)
        };
    </script>
    @vite(['resources/js/staff/dashboard.js'])
@endpush
