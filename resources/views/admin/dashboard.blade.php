@extends('layouts.admin')

@section('title', 'Dashboard & Analytics')

@push('styles')
    @vite('resources/css/dashboard.css')
@endpush

@section('content')
<div class="space-y-8 pb-12">

    <!-- Top Title Section -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Dashboard & Analytics
        </h1>
        <p class="text-sm text-slate-500 mt-1 font-normal">
            Ringkasan data dan aktivitas sistem Lensa MBG secara real-time.
        </p>
    </div>

    <!-- 4 KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total SPPG -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-[0_2px_10px_-3px_rgba(0,0,0,0.03)] flex items-center gap-4 transition-all duration-200 hover:shadow-md hover:border-blue-200">
            <div class="w-14 h-14 rounded-2xl bg-[#EFF6FF] flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-[#3B82F6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                    <path d="M9 22v-4h6v4"></path>
                    <path d="M8 6h.01"></path>
                    <path d="M16 6h.01"></path>
                    <path d="M8 10h.01"></path>
                    <path d="M16 10h.01"></path>
                    <path d="M8 14h.01"></path>
                    <path d="M16 14h.01"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600">Total SPPG</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-0.5">{{ $stats['total_sppg'] ?? 128 }}</h3>
                <p class="text-[11px] text-slate-400 font-medium">SPPG Terdaftar</p>
            </div>
        </div>

        <!-- Belum Verifikasi -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-[0_2px_10px_-3px_rgba(0,0,0,0.03)] flex items-center gap-4 transition-all duration-200 hover:shadow-md hover:border-amber-200">
            <div class="w-14 h-14 rounded-2xl bg-[#FFFBEB] flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-[#F59E0B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600">Belum Verifikasi</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-0.5">{{ $stats['belum_verifikasi'] ?? 128 }}</h3>
                <p class="text-[11px] text-slate-400 font-medium">Menunggu Verifikasi</p>
            </div>
        </div>

        <!-- Status Aktif -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-[0_2px_10px_-3px_rgba(0,0,0,0.03)] flex items-center gap-4 transition-all duration-200 hover:shadow-md hover:border-emerald-200">
            <div class="w-14 h-14 rounded-2xl bg-[#DCFCE7] flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-[#10B981]" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600">Status Aktif</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-0.5">{{ $stats['status_aktif'] ?? 128 }}</h3>
                <p class="text-[11px] text-slate-400 font-medium">SPPG Aktif</p>
            </div>
        </div>

        <!-- Status Nonaktif -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-[0_2px_10px_-3px_rgba(0,0,0,0.03)] flex items-center gap-4 transition-all duration-200 hover:shadow-md hover:border-rose-200">
            <div class="w-14 h-14 rounded-2xl bg-[#FEE2E2] flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-[#EF4444]" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M12 2C6.47 2 2 6.48 2 12s4.47 10 10 10 10-4.48 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600">Status Nonaktif</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-0.5">{{ $stats['status_nonaktif'] ?? 128 }}</h3>
                <p class="text-[11px] text-slate-400 font-medium">SPPG Nonaktif</p>
            </div>
        </div>

    </div>

    <!-- First Chart Section: Tren Pendaftaran SPPG -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/70 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900">
                    Tren Pendaftaran SPPG
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 font-normal">
                    Jumlah pendaftaran berdasarkan status dalam periode tertentu.
                </p>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Periode</label>
                    <div class="relative">
                        <select id="periodeFilter" class="appearance-none bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-700 py-1.5 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="current">1 Okt 2026 - 31 Okt 2026</option>
                            <option value="last_month">1 Sep 2026 - 30 Sep 2026</option>
                            <option value="year">Tahun 2026</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Status</label>
                    <div class="relative">
                        <select id="statusFilter" class="appearance-none bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-700 py-1.5 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="all">Semua Status</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="menunggu">Menunggu</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="koreksi">Koreksi</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas Line Chart -->
        <div class="h-64 sm:h-72 w-full relative">
            <canvas id="trenSppgChart"></canvas>
        </div>

        <!-- Legend matching mockup exactly -->
        <div class="flex items-center gap-6 mt-4 pt-4 border-t border-slate-100 text-xs font-medium text-slate-700 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#10B981]"></span>
                <span>Disetujui</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#3B82F6]"></span>
                <span>Menunggu</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#EF4444]"></span>
                <span>Ditolak</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span>
                <span>Koreksi</span>
            </div>
        </div>
    </div>

    <!-- Second Chart Section: Status Pengajuan Koreksi -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/70 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900">
                    Status Pengajuan Koreksi
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 font-normal">
                    Jumlah pengajuan koreksi berdasarkan status.
                </p>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Periode</label>
                    <div class="relative">
                        <select id="koreksiPeriodeFilter" class="appearance-none bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-700 py-1.5 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="current">1 Okt 2026 - 31 Okt 2026</option>
                            <option value="last_month">1 Sep 2026 - 30 Sep 2026</option>
                            <option value="year">Tahun 2026</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">SPPG</label>
                    <div class="relative">
                        <select id="sppgFilter" class="appearance-none bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-700 py-1.5 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="all">Semua SPPG</option>
                            @foreach($sppgList as $sppg)
                                <option value="{{ $sppg->id_sppg }}">{{ $sppg->nama_sppg }}</option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas Bar Chart -->
        <div class="h-60 w-full relative">
            <canvas id="statusKoreksiChart"></canvas>
        </div>
    </div>

    <!-- Third Section: Preview Fitur Utama -->
    <div>
        <div class="mb-5">
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Preview Fitur Utama
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">
                Aktivitas LensaMBG (pengelolaan akun, pengajuan koreksi, dan riwayat aktivitas sistem)
            </p>
        </div>

        <!-- 3 Feature Preview Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
            
            <!-- Card 1: SPPG Management -->
            <div class="flex flex-col">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex-1 flex flex-col justify-between">
                    <div>
                        <!-- Card Header -->
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">SPPG Management</h3>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 font-normal mb-4 leading-relaxed">
                            Kelola akun pengguna SPPG, atur role dan status akses.
                        </p>

                        <!-- Mini Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-700">
                                        <th class="pb-2.5 pr-3">Nama</th>
                                        <th class="pb-2.5 pr-3">SPPG</th>
                                        <th class="pb-2.5 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100/70">
                                    @foreach($userPreview as $item)
                                    <tr>
                                        <td class="py-2.5 pr-3 font-medium text-slate-800 text-[11px] whitespace-nowrap">{{ $item['nama'] }}</td>
                                        <td class="py-2.5 pr-3 text-slate-500 text-[11px] whitespace-nowrap">{{ $item['sppg'] }}</td>
                                        <td class="py-2.5 text-right space-y-1 whitespace-nowrap">
                                            @if($item['is_verified'])
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#CCFBF1] text-[#0F766E]">
                                                    Terverifikasi
                                                </span>
                                            @else
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#FEF3C7] text-[#B45309]">
                                                    Belum Diverifikasi
                                                </span>
                                            @endif
                                            <div>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#DCFCE7] text-[#15803D]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#15803D]"></span>
                                                    {{ $item['status_akun'] }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Button Outside / Attached Below -->
                <div class="mt-4">
                    <a href="{{ route('admin.sppg.index') }}"
                       class="w-full bg-white hover:bg-slate-50 border border-slate-300 rounded-xl py-2.5 px-4 text-xs font-semibold text-slate-700 flex items-center justify-center gap-2 transition-colors duration-150 shadow-sm group">
                        <span>Lihat Selengkapnya</span>
                        <svg class="w-4 h-4 text-slate-600 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Card 2: Correction Request -->
            <div class="flex flex-col">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex-1 flex flex-col justify-between">
                    <div>
                        <!-- Card Header -->
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Correction Request</h3>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 font-normal mb-4 leading-relaxed">
                            Kelola akun pengguna SPPG, atur role dan status akses.
                        </p>

                        <!-- Mini Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-700">
                                        <th class="pb-2.5 pr-3">ID Pengajuan</th>
                                        <th class="pb-2.5 pr-3">Jenis Data</th>
                                        <th class="pb-2.5 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100/70">
                                    @foreach($correctionPreview as $cr)
                                    <tr>
                                        <td class="py-2.5 pr-3 font-medium text-slate-700 text-[11px] whitespace-nowrap">{{ $cr['id'] }}</td>
                                        <td class="py-2.5 pr-3 text-slate-500 text-[11px] whitespace-nowrap">{{ $cr['jenis_data'] }}</td>
                                        <td class="py-2.5 text-right whitespace-nowrap">
                                            @if($cr['status'] === 'Menunggu')
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-[#FEF3C7] text-[#D97706]">
                                                    Menunggu
                                                </span>
                                            @elseif($cr['status'] === 'Disetujui')
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-[#DCFCE7] text-[#16A34A]">
                                                    Disetujui
                                                </span>
                                            @else
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-[#FEE2E2] text-[#DC2626]">
                                                    Ditolak
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Button Outside / Attached Below -->
                <div class="mt-4">
                    <a href="{{ route('admin.correction-requests.index') }}"
                       class="w-full bg-white hover:bg-slate-50 border border-slate-300 rounded-xl py-2.5 px-4 text-xs font-semibold text-slate-700 flex items-center justify-center gap-2 transition-colors duration-150 shadow-sm group">
                        <span>Lihat Selengkapnya</span>
                        <svg class="w-4 h-4 text-slate-600 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Card 3: Audit Trail -->
            <div class="flex flex-col">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex-1 flex flex-col justify-between">
                    <div>
                        <!-- Card Header -->
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                    <path d="M3 3v5h5"></path>
                                    <polyline points="12 7 12 12 15 15"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Audit Trail</h3>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 font-normal mb-4 leading-relaxed">
                            Kelola akun pengguna SPPG, atur role dan status akses.
                        </p>

                        <!-- Mini Table with generous row height and column separation -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-700">
                                        <th class="pb-3 pr-2 whitespace-nowrap">Waktu</th>
                                        <th class="pb-3 px-3 whitespace-nowrap">Pengguna</th>
                                        <th class="pb-3 px-3 whitespace-nowrap">Modul</th>
                                        <th class="pb-3 pl-2 whitespace-nowrap">Aktivitas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100/70">
                                    @foreach($auditPreview as $log)
                                    <tr>
                                        <td class="py-4 pr-2 text-slate-600 text-[11px] whitespace-nowrap align-middle">{{ $log['waktu'] }}</td>
                                        <td class="py-4 px-3 font-medium text-slate-700 text-[11px] leading-tight whitespace-nowrap align-middle">
                                            <div class="font-semibold text-slate-800">{{ explode(' ', $log['pengguna'])[0] }}</div>
                                            <div class="text-[10px] text-slate-400">({{ str_contains($log['pengguna'], 'Admin') ? 'Admin' : 'SPPG' }})</div>
                                        </td>
                                        <td class="py-4 px-3 text-slate-600 text-[11px] whitespace-nowrap font-medium align-middle">{{ $log['modul'] }}</td>
                                        <td class="py-4 pl-2 text-slate-700 text-[11px] font-medium leading-tight whitespace-nowrap align-middle">{{ $log['aktivitas'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Action Button Outside / Attached Below -->
                <div class="mt-4">
                    <a href="{{ route('admin.audit-trail.index') }}"
                       class="w-full bg-white hover:bg-slate-50 border border-slate-300 rounded-xl py-2.5 px-4 text-xs font-semibold text-slate-700 flex items-center justify-center gap-2 transition-colors duration-150 shadow-sm group">
                        <span>Lihat Selengkapnya</span>
                        <svg class="w-4 h-4 text-slate-600 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
@vite('resources/js/dashboard.js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initDashboardCharts === 'function') {
        window.initDashboardCharts({
            trenChartData: @json($trenChartData),
            sppgCrPeriodMap: @json($sppgCrPeriodMap),
            koreksiChartData: @json($koreksiChartData)
        });
    }
});
</script>
@endpush
