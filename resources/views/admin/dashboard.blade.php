@extends('layouts.admin')

@section('title', 'Dashboard & Analytics')

@push('styles')
    @vite('resources/css/admin/dashboard.css')
@endpush

@section('content')
<div class="space-y-7">

    {{-- Title & Subtitle --}}
    <div class="page-title-section">
        <h1 class="page-title">
            Selamat datang {{ Auth::user()->nama ?? 'Admin' }} di Dashboard & Analytics
        </h1>
        <p class="page-subtitle">
            Ringkasan data dan aktivitas sistem Lensa MBG secara real-time.
        </p>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="stat-cards-grid">
        {{-- Total SPPG --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                    <path d="M9 22v-4h6v4"></path>
                    <rect x="8" y="6" width="2" height="2"></rect>
                    <rect x="14" y="6" width="2" height="2"></rect>
                    <rect x="8" y="11" width="2" height="2"></rect>
                    <rect x="14" y="11" width="2" height="2"></rect>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total SPPG</span>
                <span class="stat-value">{{ $stats['total_sppg'] ?? 128 }}</span>
                <span class="stat-subtext">SPPG Terdaftar</span>
            </div>
        </div>

        {{-- Belum Verifikasi --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Belum Verifikasi</span>
                <span class="stat-value">{{ $stats['belum_verifikasi'] ?? 128 }}</span>
                <span class="stat-subtext">Menunggu Verifikasi</span>
            </div>
        </div>

        {{-- Status Aktif --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Status Aktif</span>
                <span class="stat-value">{{ $stats['status_aktif'] ?? 128 }}</span>
                <span class="stat-subtext">SPPG Aktif</span>
            </div>
        </div>

        {{-- Status Nonaktif --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-red">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M12 2C6.47 2 2 6.48 2 12s4.47 10 10 10 10-4.48 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Status Nonaktif</span>
                <span class="stat-value">{{ $stats['status_nonaktif'] ?? 128 }}</span>
                <span class="stat-subtext">SPPG Nonaktif</span>
            </div>
        </div>
    </div>

    {{-- Charts Section: 2 Columns (Tren Pendaftaran SPPG & Distribusi Status SPPG) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        
        {{-- Visualisasi 1: Tren Pendaftaran SPPG --}}
        <div class="dashboard-card flex flex-col justify-between">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="dashboard-card-title">
                        Tren Pendaftaran SPPG
                    </h2>
                </div>

                <div class="text-right">
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 text-right">Periode</label>
                    <div class="relative inline-block text-left">
                        <select id="trenPeriodeFilter" class="select-filter-box">
                            <option value="2026" selected>2026</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="h-64 sm:h-72 w-full relative">
                <canvas id="trenSppgChart"></canvas>
            </div>
        </div>

        {{-- Visualisasi 2 & 3: Distribusi Status SPPG (Switchable Donut & Bar) --}}
        <div class="dashboard-card flex flex-col justify-between">
            <div class="flex items-center justify-between gap-4 mb-2">
                <div>
                    <h2 class="dashboard-card-title">
                        Distribusi Status SPPG
                    </h2>
                </div>

                <div class="text-right">
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 text-right">Visualisasi</label>
                    <div class="relative inline-block text-left">
                        <select id="distribusiVisualisasiFilter" class="select-filter-box">
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
                <canvas id="distribusiStatusChart"></canvas>
            </div>

            {{-- Legend Warna Sesuai Keterangan --}}
            <div class="flex items-center justify-center gap-7 mt-4 pt-4 border-t border-slate-100 text-xs font-semibold text-slate-700 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#2563EB]"></span>
                    <span>Aktif</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#F59E0B]"></span>
                    <span>Menunggu Verifikasi</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#EF4444]"></span>
                    <span>Nonaktif</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Section: Preview Fitur Utama (2 Cards: SPPG Management & Audit Trail) --}}
    <div>
        <div class="mb-5">
            <h2 class="text-xl sm:text-2xl font-bold text-[#071E49] tracking-tight">
                Preview Fitur Utama
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">
                Aktivitas LensaMBG (pengelolaan sppg, dan riwayat aktivitas sistem)
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            
            {{-- Card 1: SPPG Management --}}
            <div class="dashboard-card flex flex-col justify-between h-full">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="dashboard-card-title">SPPG Management</h3>
                        </div>
                    </div>
                    <p class="dashboard-card-subtitle mb-4">
                        Kelola akun pengguna SPPG, atur role dan status akses.
                    </p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-700">
                                    <th class="pb-2.5 pr-3 font-semibold text-slate-700">SPPG</th>
                                    <th class="pb-2.5 pr-3 font-semibold text-slate-700">Kepala SPPG</th>
                                    <th class="pb-2.5 pr-3 font-semibold text-slate-700">Staff SPPG</th>
                                    <th class="pb-2.5 text-center font-semibold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/80">
                                @foreach($sppgPreview as $item)
                                <tr class="preview-table-row">
                                    <td class="pr-3 font-medium text-slate-800 text-[11px] whitespace-nowrap">{{ $item['sppg'] }}</td>
                                    <td class="pr-3 font-medium text-slate-700 text-[11px] whitespace-nowrap">{{ $item['kepala_sppg'] }}</td>
                                    <td class="pr-3 font-medium text-slate-700 text-[11px] whitespace-nowrap">{{ $item['staff_sppg'] }}</td>
                                    <td class="text-center whitespace-nowrap">
                                        @if($item['is_verified'])
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-[#DCFCE7] text-[#16A34A]">
                                                Terverifikasi
                                            </span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-[#FEF3C7] text-[#D97706]">
                                                Belum Diverifikasi
                                            </span>
                                        @endif
                                        <div class="mt-0.5">
                                            @if(strtolower($item['status_operasional']) === 'aktif')
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9.5px] font-semibold bg-[#DCFCE7] text-[#16A34A]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9.5px] font-semibold bg-[#FEE2E2] text-[#DC2626]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626]"></span>
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-5">
                    <a href="{{ route('admin.sppg.index') }}" class="btn-detail-link group">
                        <span>Lihat Selengkapnya</span>
                        <svg class="w-4 h-4 text-slate-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Card 2: Audit Trail --}}
            <div class="dashboard-card flex flex-col justify-between h-full">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                                <polyline points="12 7 12 12 15 15"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h3 class="dashboard-card-title">Audit Trail</h3>
                        </div>
                    </div>
                    <p class="dashboard-card-subtitle mb-4">
                        Kelola akun pengguna SPPG, atur role dan status akses.
                    </p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-700">
                                    <th class="pb-2.5 pr-2 whitespace-nowrap font-semibold text-slate-700">Waktu</th>
                                    <th class="pb-2.5 px-3 whitespace-nowrap font-semibold text-slate-700">Pengguna</th>
                                    <th class="pb-2.5 px-3 whitespace-nowrap font-semibold text-slate-700">Modul</th>
                                    <th class="pb-2.5 pl-2 whitespace-nowrap font-semibold text-slate-700">Aktivitas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/80">
                                @foreach($auditPreview as $log)
                                <tr class="preview-table-row">
                                    <td class="pr-2 text-slate-600 text-[11px] whitespace-nowrap align-middle">{{ $log['waktu'] }}</td>
                                    <td class="px-3 font-medium text-slate-700 text-[11px] leading-tight whitespace-nowrap align-middle">
                                        <div class="font-semibold text-slate-800">{{ $log['pengguna'] }}</div>
                                        <div class="text-[10px] text-slate-400">({{ $log['role'] }})</div>
                                    </td>
                                    <td class="px-3 text-slate-600 text-[11px] whitespace-nowrap font-medium align-middle">{{ $log['modul'] }}</td>
                                    <td class="pl-2 text-slate-700 text-[11px] font-medium leading-tight whitespace-nowrap align-middle">{{ $log['aktivitas'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-5">
                    <a href="{{ route('admin.audit') }}" class="btn-detail-link group">
                        <span>Lihat Selengkapnya</span>
                        <svg class="w-4 h-4 text-slate-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
@vite('resources/js/admin/dashboard.js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initDashboardCharts === 'function') {
        window.initDashboardCharts({
            trenChartData: @json($trenChartData),
            distribusiStatusData: @json($distribusiStatusData)
        });
    }
});
</script>
@endpush
