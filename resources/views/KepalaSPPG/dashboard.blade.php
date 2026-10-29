@extends('layouts.kepala-sppg')

@section('title', 'Dashboard - Kepala SPPG')

@push('styles')
    @vite('resources/css/KepalaSPPG/dashboard.css')
@endpush

@section('main-class', 'content-body')

@section('content')
<div class="dashboard-page-container">

    {{-- Page Header --}}
    <div class="dashboard-header-section">
        <div>
            <h1 class="dashboard-title">Dashboard</h1>
            <p class="dashboard-subtitle">
                <span>{{ $todayStr }}</span>
                <span class="bullet-divider">•</span>
                <span class="font-medium text-slate-700">{{ $sppg->nama_sppg ?? 'SPPG Melati Sukorejo' }} ({{ 'SPPG' . str_pad($sppg->id_sppg ?? 1, 3, '0', STR_PAD_LEFT) }})</span>
            </p>
        </div>
    </div>

    {{-- 1. PERLU TINDAKAN (Action Required Cards) --}}
    <section class="dashboard-section">
        <h2 class="section-label">1. PERLU TINDAKAN</h2>
        <div class="action-cards-grid">
            {{-- Card 1: Koreksi Menunggu --}}
            <div class="action-card card-koreksi">
                <div class="action-card-header">
                    <span class="action-card-title">Pengajuan koreksi menunggu</span>
                </div>
                <div class="action-card-value text-[#071E49]">{{ $pendingCorrectionsCount }}</div>
                <p class="action-card-desc">{{ $correctionSummary }}</p>
                <div class="action-card-footer">
                    <a href="{{ route('sppg.correction') }}" class="btn-action-primary">
                        <span>Tinjau koreksi</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Card 2: Bahan Perlu Perhatian --}}
            <div class="action-card card-bahan">
                <div class="action-card-header">
                    <span class="action-card-title">Bahan perlu perhatian</span>
                </div>
                <div class="action-card-value text-[#071E49]">{{ $criticalMaterialsCount }}</div>
                <p class="action-card-desc">{{ $criticalMaterialsSummary }}</p>
                <div class="action-card-footer">
                    <a href="#section-stok-perhatian" class="btn-action-secondary">
                        <span>Lihat di bawah</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Card 3: Status SPPG --}}
            <div class="action-card card-status">
                <div class="action-card-header">
                    <span class="action-card-title">Status SPPG</span>
                </div>
                <div class="action-card-badge-wrap">
                    <span class="badge-status-aktif">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>{{ $sppgStatusLabel }}</span>
                    </span>
                </div>
                <p class="action-card-desc">{{ $sppgInfoText }}</p>
                <div class="action-card-footer">
                    <button type="button" class="btn-action-secondary" onclick="openProfilModal()">
                        <span>Data kepala</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                    <a href="{{ route('sppg.staff.index') }}" class="btn-action-primary">
                        <span>Kelola staff</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. RINGKASAN HARI INI (4 Stat Cards Khas Lensa MBG) --}}
    <section class="dashboard-section">
        <h2 class="section-label">2. RINGKASAN HARI INI</h2>
        <div class="stat-cards-grid">
            {{-- Card 1: Batch Hari Ini --}}
            <div class="stat-card">
                <div class="stat-icon-wrapper icon-blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"></path>
                        <line x1="6" y1="17" x2="18" y2="17"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Batch hari ini</span>
                    <span class="stat-value">{{ $ringkasan['batch_count'] }}</span>
                    <span class="stat-subtext">{{ $ringkasan['batch_diproses'] }} diproses</span>
                </div>
            </div>

            {{-- Card 2: Porsi Siap / Target --}}
            <div class="stat-card">
                <div class="stat-icon-wrapper icon-yellow">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 14 14"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Porsi siap / target</span>
                    <span class="stat-value">{{ $ringkasan['porsi_siap'] }} / {{ $ringkasan['porsi_target'] }}</span>
                    <span class="stat-subtext">Batch belum selesai</span>
                </div>
            </div>

            {{-- Card 3: Porsi Terkirim Kemarin --}}
            <div class="stat-card">
                <div class="stat-icon-wrapper icon-green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Porsi terkirim kemarin</span>
                    <span class="stat-value">{{ $ringkasan['porsi_terkirim_kemarin'] }}</span>
                    <span class="stat-subtext">dari {{ $ringkasan['porsi_target_kemarin'] }} porsi</span>
                </div>
            </div>

            {{-- Card 4: Stok Kritis --}}
            <div class="stat-card">
                <div class="stat-icon-wrapper icon-red">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Stok kritis</span>
                    <span class="stat-value">{{ $ringkasan['stok_kritis_count'] }} bahan</span>
                    <span class="stat-subtext">{{ $ringkasan['stok_kritis_detail'] }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. ALUR PROSES HARI INI (Pipeline Stepper) --}}
    <section class="dashboard-section">
        <div class="dashboard-card p-5">
            <div class="section-card-header">
                <h3 class="dashboard-card-title">3. Alur proses hari ini</h3>
                <span class="card-header-date">{{ Carbon\Carbon::now()->locale('id')->translatedFormat('d M Y') }}</span>
            </div>

            <div class="process-flow-container">
                <div class="process-flow-track" aria-hidden="true"></div>

                <div class="process-flow-steps">
                    @foreach($alurProses as $step)
                        <div class="process-step-item {{ $step['state'] }}">
                            <div class="step-node-bubble">
                                @if($step['state'] === 'completed')
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                @else
                                    <span>{{ $step['step'] }}</span>
                                @endif
                            </div>
                            <span class="step-title">{{ $step['name'] }}</span>
                            <span class="step-desc">{{ $step['desc'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ROW: 4. BATCH TERBARU & 5. DISTRIBUSI TERBARU --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-stretch">
        {{-- 4. Batch Terbaru (7 cols) --}}
        <div class="lg:col-span-7 flex">
            <div class="dashboard-card p-5 flex flex-col justify-between w-full">
                <div>
                    <div class="section-card-header">
                        <h3 class="dashboard-card-title">4. Batch terbaru</h3>
                        <span class="card-header-tag">3 terakhir</span>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>KODE</th>
                                    <th>MENU</th>
                                    <th class="text-center">TARGET / HASIL</th>
                                    <th class="text-right">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batchTerbaru as $batch)
                                    <tr>
                                        <td class="font-semibold text-[#071E49] whitespace-nowrap">{{ $batch['kode'] }}</td>
                                        <td class="text-slate-700 min-w-[160px]">{{ $batch['menu'] }}</td>
                                        <td class="text-center text-slate-600 whitespace-nowrap">{{ $batch['target_porsi'] }} / {{ $batch['actual_porsi'] }}</td>
                                        <td class="text-right whitespace-nowrap">
                                            @php
                                                $st = strtolower($batch['status']);
                                                $badgeClass = match(true) {
                                                    str_contains($st, 'masak') || str_contains($st, 'cook') => 'badge-pill-amber',
                                                    str_contains($st, 'distribusi') => 'badge-pill-blue',
                                                    str_contains($st, 'selesai') => 'badge-pill-green',
                                                    default => 'badge-pill-slate',
                                                };
                                            @endphp
                                            <span class="badge-pill {{ $badgeClass }}">{{ $batch['status'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-6 text-slate-400">Belum ada batch produksi tercatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Distribusi Terbaru (5 cols) --}}
        <div class="lg:col-span-5 flex">
            <div class="dashboard-card p-5 flex flex-col justify-between w-full">
                <div>
                    <div class="section-card-header">
                        <h3 class="dashboard-card-title">5. Distribusi terbaru</h3>
                        <span class="card-header-tag">{{ Carbon\Carbon::now()->subDay()->translatedFormat('d M') }}</span>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>TUJUAN</th>
                                    <th class="text-center">PORSI</th>
                                    <th class="text-right">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distribusiTerbaru as $dist)
                                    <tr>
                                        <td class="font-medium text-[#071E49]">{{ $dist['tujuan'] }}</td>
                                        <td class="text-center font-semibold text-slate-700">{{ $dist['porsi'] }}</td>
                                        <td class="text-right whitespace-nowrap">
                                            @php
                                                $dSt = strtolower($dist['status']);
                                                $distClass = match(true) {
                                                    str_contains($dSt, 'terkirim') => 'badge-pill-green',
                                                    str_contains($dSt, 'rencana') => 'badge-pill-slate',
                                                    default => 'badge-pill-blue',
                                                };
                                            @endphp
                                            <span class="badge-pill {{ $distClass }}">{{ $dist['status'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-6 text-slate-400">Belum ada catatan distribusi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footnote-alert">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>{{ $distribusiFooter }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW: 6. STOK PERLU PERHATIAN & 7. GRAFIK 7 HARI --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-stretch" id="section-stok-perhatian">
        {{-- 6. Stok Perlu Perhatian (6 cols) --}}
        <div class="lg:col-span-6 flex">
            <div class="dashboard-card p-5 flex flex-col justify-between w-full">
                <div>
                    <div class="section-card-header">
                        <h3 class="dashboard-card-title">6. Stok perlu perhatian</h3>
                        <span class="card-header-tag">{{ count($stokPerhatian) }} bahan</span>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($stokPerhatian as $item)
                            <div class="stock-alert-item">
                                <div class="stock-alert-info">
                                    <div class="stock-alert-header">
                                        <span class="stock-item-name">{{ $item['nama'] }}</span>
                                        <span class="stock-item-code">{{ $item['kode'] }}</span>
                                    </div>
                                    <div class="stock-item-subtext">
                                        Sisa {{ $item['sisa'] }} <span class="bullet-divider">•</span> minimum {{ $item['minimum'] }} <span class="bullet-divider">•</span> exp {{ $item['exp'] }}
                                    </div>
                                </div>
                                <div class="stock-alert-badge-wrap">
                                    @if($item['status_type'] === 'danger')
                                        <span class="badge-pill badge-pill-danger">{{ $item['status_label'] }}</span>
                                    @else
                                        <span class="badge-pill badge-pill-amber">{{ $item['status_label'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. Grafik 7 Hari (6 cols) --}}
        <div class="lg:col-span-6 flex">
            <div class="dashboard-card p-5 flex flex-col justify-between w-full">
                <div class="section-card-header">
                    <h3 class="dashboard-card-title">7. Grafik 7 hari</h3>
                    <span class="card-header-tag">{{ $chartDateRange }}</span>
                </div>

                {{-- Chart container --}}
                <div class="chart-container-wrapper relative w-full h-[160px] my-auto">
                    <canvas id="dashboard7HariChart"></canvas>
                </div>

                {{-- Legend & Summary Footnote --}}
                <div class="chart-footer-meta">
                    <div class="chart-legend-row">
                        <div class="chart-legend-item">
                            <span class="legend-color-dot dot-produksi"></span>
                            <span>Porsi diproduksi</span>
                        </div>
                        <div class="chart-legend-item">
                            <span class="legend-color-dot dot-terkirim"></span>
                            <span>Porsi terkirim</span>
                        </div>
                    </div>
                    <p class="chart-summary-text">{{ $chartSusutLimbahText }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 8. AKTIVITAS TERBARU (Audit Trail 5 Terakhir) --}}
    <section class="dashboard-section">
        <div class="dashboard-card p-5">
            <div class="section-card-header">
                <h3 class="dashboard-card-title">8. Aktivitas terbaru</h3>
                <span class="card-header-tag">dari audit trail</span>
            </div>

            <div class="audit-timeline-feed">
                @forelse($aktivitasTerbaru as $log)
                    <div class="audit-timeline-item">
                        <div class="audit-item-marker"></div>
                        <div class="audit-item-content">
                            <span class="audit-actor-name">{{ $log['aktor'] }}</span>
                            <span class="audit-action-text">{{ $log['aksi'] }}</span>
                            <span class="audit-code-badge">{{ $log['kode'] }}</span>
                            <span class="audit-time-text">• {{ $log['waktu'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">Belum ada riwayat aktivitas terbaru.</div>
                @endforelse
            </div>

            <div class="audit-footer-action mt-5 pt-4 border-t border-slate-100 flex justify-start">
                <a href="{{ route('sppg.audit') }}" class="btn-audit-trail">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>Lihat semua audit trail</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
    {{-- Chart.js for 7-day Bar Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.dashboardChartConfig = {
            labels: @json($chartDays),
            produksi: @json($chartProduksi),
            terkirim: @json($chartTerkirim)
        };
    </script>
    @vite('resources/js/KepalaSPPG/dashboard.js')
@endpush
