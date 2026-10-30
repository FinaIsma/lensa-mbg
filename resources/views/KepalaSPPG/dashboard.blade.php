@extends('layouts.kepala-sppg')

@section('title', 'Dashboard Operasional - Kepala SPPG')

@push('styles')
    @vite('resources/css/KepalaSPPG/dashboard.css')
@endpush

@section('main-class', 'content-body')

@section('content')
<div class="space-y-7 pb-8">

    {{-- Title & Subtitle Section --}}
    <div class="page-title-section">
        <h1 class="page-title">
            Selamat datang, {{ $user->nama ?? 'Kepala SPPG' }} di Dashboard Operasional SPPG
        </h1>
        <p class="page-subtitle">
            Ringkasan data dan aktivitas operasional SPPG secara real-time.
        </p>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Porsi Siap / Target --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Porsi Siap / Target</span>
                <span class="stat-value">{{ $stats['porsi_siap'] }} / {{ $stats['porsi_target'] }}</span>
                <span class="stat-subtext">{{ $stats['batch_count'] }} Batch Terjadwal</span>
            </div>
        </div>

        {{-- Card 2: Porsi Terdistribusi --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Porsi Terdistribusi</span>
                <span class="stat-value">{{ $stats['porsi_terdistribusi'] }}</span>
                <span class="stat-subtext">Tingkat Capaian {{ $stats['distribusi_pct'] }}%</span>
            </div>
        </div>

        {{-- Card 3: Koreksi Menunggu --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Koreksi Menunggu</span>
                <span class="stat-value">{{ $stats['koreksi_menunggu'] }}</span>
                <a href="{{ route('sppg.correction') }}" class="stat-subtext hover:text-amber-700 hover:underline inline-flex items-center gap-1 font-semibold">
                    <span>Tinjau Sekarang</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        {{-- Card 4: Stok Kritis --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-red">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Stok Perlu Perhatian</span>
                <span class="stat-value">{{ $stats['stok_kritis'] }} Bahan</span>
                <span class="stat-subtext">Di Bawah Batas Min / Exp</span>
            </div>
        </div>
    </div>

    {{-- Charts Section: 2 Columns (Tren Produksi & Distribusi vs Kesehatan Stok) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        
        {{-- Visualisasi 1: Tren Produksi & Distribusi (Smooth Line Chart with Area Gradient) --}}
        <div class="dashboard-card flex flex-col justify-between">
            <div class="flex items-center justify-between gap-4 mb-3">
                <div>
                    <h2 class="dashboard-card-title">Tren Produksi & Distribusi</h2>
                    <p class="dashboard-card-subtitle">Perbandingan volume masak dan porsi sukses terkirim</p>
                </div>

                <div class="text-right">
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 text-right">Periode</label>
                    <div class="relative inline-block text-left">
                        <select id="trenPeriodeFilter" class="select-filter-box">
                            <option value="7hari" selected>7 Hari Terakhir</option>
                            <option value="30hari">30 Hari Terakhir</option>
                            <option value="2026">Tahun 2026</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="h-64 sm:h-72 w-full relative">
                <canvas id="trenProduksiDistribusiChart"></canvas>
            </div>

            {{-- Legend Warna --}}
            <div class="flex items-center justify-center gap-7 mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-slate-700 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#071E49]"></span>
                    <span>Porsi Diproduksi</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#2563EB]"></span>
                    <span>Porsi Terdistribusi</span>
                </div>
            </div>
        </div>

        {{-- Visualisasi 2: Kondisi Kesehatan Stok Bahan (Donut & Bar) --}}
        <div class="dashboard-card flex flex-col justify-between">
            <div class="flex items-center justify-between gap-4 mb-3">
                <div>
                    <h2 class="dashboard-card-title">Kondisi Kesehatan Stok Bahan</h2>
                    <p class="dashboard-card-subtitle">Pemetaan rasio kecukupan inventaris bahan baku</p>
                </div>

                <div class="text-right">
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 text-right">Visualisasi</label>
                    <div class="relative inline-block text-left">
                        <select id="stokVisualisasiFilter" class="select-filter-box">
                            <option value="donut" selected>Diagram Donat</option>
                            <option value="bar">Diagram Batang</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="h-64 sm:h-72 w-full relative flex items-center justify-center">
                <canvas id="kesehatanStokChart"></canvas>
            </div>

            {{-- Legend Warna --}}
            <div class="flex items-center justify-center gap-6 mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-slate-700 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#16A34A]"></span>
                    <span>Stok Aman</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#F59E0B]"></span>
                    <span>Mendekati Minimum</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#EF4444]"></span>
                    <span>Kritis / Expired</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Row 3: Alur Operasional Dapur (1 Card Memanjang Full Width) --}}
    <div class="dashboard-card p-5 w-full">
        <div class="flex items-center justify-between gap-4 mb-3">
            <div>
                <h3 class="dashboard-card-title">Alur Operasional Dapur</h3>
                <p class="dashboard-card-subtitle">Tahapan produksi makanan bergizi hari ini</p>
            </div>
            <span class="text-xs font-medium text-slate-500 bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
                {{ Carbon\Carbon::now()->locale('id')->translatedFormat('d M Y') }}
            </span>
        </div>

        <div class="process-flow-container my-auto py-2">
            <div class="process-flow-track" aria-hidden="true"></div>

            <div class="process-flow-steps">
                @foreach($alurProses as $step)
                    <div class="process-step-item {{ $step['state'] }}">
                        <div class="step-node-bubble">
                            @if($step['state'] === 'completed')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
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

    {{-- Row 4: Batch Terbaru & Distribusi Terbaru (Formasi 2 Kolom Berdampingan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        {{-- Card Kiri: Batch Terbaru --}}
        <div class="dashboard-card p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-4 mb-3">
                    <div>
                        <h3 class="dashboard-card-title">Batch Terbaru</h3>
                        <p class="dashboard-card-subtitle">3 batch terakhir diproses di dapur</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">
                        Produksi
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-700">
                                <th class="pb-2 font-semibold">Kode & Menu</th>
                                <th class="pb-2 text-center font-semibold">Target / Hasil</th>
                                <th class="pb-2 text-right font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/80">
                            @foreach($batchTerbaru as $b)
                            <tr class="h-10">
                                <td class="pr-2 align-middle">
                                    <div class="font-bold text-[#071E49] text-[11.5px]">{{ $b['kode'] }}</div>
                                    <div class="text-[10.5px] text-slate-500 truncate max-w-[190px]">{{ $b['menu'] }}</div>
                                </td>
                                <td class="text-center font-semibold text-slate-700 text-[11.5px] align-middle whitespace-nowrap">
                                    {{ $b['hasil'] }} / {{ $b['target'] }} porsi
                                </td>
                                <td class="text-right align-middle whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $b['status_badge'] }}">
                                        {{ $b['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-400">
                Satu batch = satu menu = satu ukuran porsi
            </div>
        </div>

        {{-- Card Kanan: Distribusi Terbaru --}}
        <div class="dashboard-card p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-4 mb-2">
                    <div>
                        <h3 class="dashboard-card-title">Distribusi Terbaru</h3>
                        <p class="dashboard-card-subtitle">Penyaluran porsi ke sekolah & posyandu</p>
                    </div>
                    <span class="text-xs font-medium text-slate-500 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-100">
                        Hari Ini
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-700">
                                <th class="pb-2 font-semibold">Tujuan</th>
                                <th class="pb-2 text-center font-semibold">Porsi</th>
                                <th class="pb-2 text-right font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/80">
                            @foreach($distribusiTerbaru as $dist)
                            <tr class="h-10">
                                <td class="pr-2 font-medium text-slate-800 text-[11.5px] whitespace-nowrap">{{ $dist['tujuan'] }}</td>
                                <td class="text-center font-bold text-[#071E49] text-[11.5px] whitespace-nowrap">{{ $dist['porsi'] }}</td>
                                <td class="text-right whitespace-nowrap">
                                    @php
                                        $st = strtolower($dist['status']);
                                        $dBadge = match(true) {
                                            str_contains($st, 'terkirim') => 'bg-[#DCFCE7] text-[#16A34A]',
                                            str_contains($st, 'rencana') => 'bg-slate-100 text-slate-600',
                                            default => 'bg-[#EBF2FE] text-[#2563EB]',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $dBadge }}">
                                        {{ $dist['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center gap-2 text-[11px] text-slate-500">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                <span>{{ $distribusiFooter }}</span>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.dashboardChartConfig = {
            trenChartData: @json($trenChartData),
            stokChartData: @json($stokChartData)
        };
    </script>
    @vite('resources/js/KepalaSPPG/dashboard.js')
@endpush
