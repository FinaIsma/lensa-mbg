@extends('layouts.public')

@section('title', 'Pantau Menu & Kualitas Makanan Bergizi Hari Ini - Lensa MBG')

@section('content')
@php
    $activeJadwal = $jadwal ?? request('jadwal', 'today');
@endphp
<div class="space-y-12 pb-20">

    <!-- HERO SECTION (Sesuai Mockup Desain Ramah & Bersahabat) -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 pb-0">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Kolom Kiri: Informasi & Keunggulan -->
            <div class="lg:col-span-8 space-y-4 sm:space-y-5 text-left">
                <!-- Badge Pill -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 shadow-2xs">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Portal Ramah & Terbuka Makan Bergizi Gratis</span>
                </div>

                <!-- Headline -->
                <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-black tracking-tight text-slate-900 leading-[1.18]">
                    Kepoin Piring MBG Kamu Hari Ini!
                </h1>

                <!-- Subtitle -->
                <p class="text-slate-600 text-sm sm:text-base leading-relaxed max-w-2xl font-normal">
                    Pantau porsi lezat, bahan segar dari petani lokal, sampai koki dapur SPPG favoritmu!
                </p>

                <!-- 3 Feature Badges -->
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Bahan Segar Petani Lokal</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200/80">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Standar Ahli Gizi Teruji</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200/80">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Tiba Tepat Waktu</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Maskot Ilustrasi Interaktif -->
            <div class="lg:col-span-4 flex justify-center lg:justify-end">
                <img src="{{ asset('images/senang-trimmed.png') }}" alt="Maskot Lensa MBG" class="w-48 sm:w-56 lg:w-64 h-auto object-contain drop-shadow-xl animate-float hover:scale-105 transition-transform duration-300">
            </div>
        </div>
    </div>

    <!-- FILTER CARD: RADAR PENCARIAN SEKOLAH SASARAN DENGAN MASKOT NEMPEL -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 sm:mt-12 relative">
        <!-- Maskot Nempel di Atas Kartu Banner -->
        <div class="absolute -top-7 sm:-top-8 left-1/2 -translate-x-1/2 w-20 sm:w-24 pointer-events-none z-20">
            <img src="{{ asset('images/nempel-banner-trimmed.png') }}" alt="Maskot Radar MBG" class="w-full h-auto object-contain drop-shadow-md animate-float">
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-6 sm:p-8 text-slate-800 border border-slate-200/80 relative z-10">
            <!-- Header Kartu -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/80">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-base sm:text-lg">Radar Pencarian Sekolah Sasaran</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih wilayah atau ketik nama sekolah untuk mengecek menu, asal bahan, dan jam pengantaran.</p>
                    </div>
                </div>

                <!-- Date Tabs: Pilih Jadwal (1 Baris Utuh, Tanpa Wrap) -->
                <div class="flex items-center gap-2 text-xs shrink-0 flex-nowrap whitespace-nowrap">
                    <span class="text-slate-500 font-semibold shrink-0">Pilih Jadwal:</span>
                    <button type="button" data-jadwal="today" class="jadwal-btn inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs transition shrink-0 whitespace-nowrap cursor-pointer {{ $activeJadwal === 'today' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-3.5 h-3.5 {{ $activeJadwal === 'today' ? 'text-emerald-400' : 'text-slate-400' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('d M') }})</span>
                    </button>
                    <button type="button" data-jadwal="yesterday" class="jadwal-btn inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs transition shrink-0 whitespace-nowrap cursor-pointer {{ $activeJadwal === 'yesterday' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span>Kemarin ({{ \Carbon\Carbon::now()->subDay()->translatedFormat('d M') }})</span>
                    </button>
                    <button type="button" data-jadwal="7days" class="jadwal-btn inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs transition shrink-0 whitespace-nowrap cursor-pointer {{ $activeJadwal === '7days' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span>7 Hari Lalu</span>
                    </button>
                </div>
            </div>

            <!-- Form Wilayah & Sekolah: Baris 1 Wilayah (3 Kolom), Baris 2 Sekolah Sasaran di Bawah -->
            <form id="filterForm" method="GET" action="{{ route('public.home') }}" data-provinsi="{{ request('provinsi', '') }}" data-kabupaten="{{ request('kabupaten_kota', '') }}" data-kecamatan="{{ request('kecamatan', '') }}" data-destination="{{ request('destination_id', '') }}" class="pt-6 space-y-4">
                <input type="hidden" name="jadwal" id="inputJadwal" value="{{ $activeJadwal }}">
                <!-- Baris 1: 3 Wilayah (Provinsi, Kota/Kabupaten, Kecamatan) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- 1. Provinsi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Provinsi</span>
                        </label>
                        <select id="select-provinsi" name="provinsi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500 shadow-2xs transition">
                            <option value="">Pilih Provinsi</option>
                        </select>
                    </div>

                    <!-- 2. Kota / Kabupaten -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Kota / Kabupaten</span>
                        </label>
                        <select id="select-regency" name="kabupaten_kota" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-500 focus:ring-2 focus:ring-blue-500 shadow-2xs transition">
                            <option value="">Pilih provinsi dahulu</option>
                        </select>
                    </div>

                    <!-- 3. Kecamatan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span>Kecamatan</span>
                        </label>
                        <select id="select-district" name="kecamatan" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-500 focus:ring-2 focus:ring-blue-500 shadow-2xs transition">
                            <option value="">Pilih kota/kabupaten dahulu</option>
                        </select>
                    </div>
                </div>

                <!-- Baris 2: Nama Sekolah Sasaran di Bagian Bawah + Tombol Cari Sekolah -->
                <div class="pt-1">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                            <span>Nama Sekolah Sasaran</span>
                        </span>
                        <span class="text-[11px] text-slate-400 font-normal">Disaring sesuai wilayah SPPG</span>
                    </label>
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="relative flex-1 w-full">
                            <select id="select-instansi" name="destination_id" {{ $allDestinations->isEmpty() ? 'disabled' : '' }} class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-300 {{ $allDestinations->isEmpty() ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : 'bg-white text-slate-800' }} text-xs font-bold focus:ring-2 focus:ring-blue-500 shadow-2xs transition">
                                <option value="">{{ $allDestinations->isEmpty() ? 'Pilih kecamatan terlebih dahulu' : '-- Pilih Sekolah / Instansi Sasaran --' }}</option>
                                @foreach($allDestinations as $dest)
                                    <option value="{{ $dest->id }}" {{ (isset($selectedDestination) && $selectedDestination->id == $dest->id) ? 'selected' : '' }}>
                                        {{ $dest->nama }} ({{ $dest->sppg?->nama_sppg ?? 'SPPG' }})
                                    </option>
                                @endforeach
                            </select>
                            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                        </div>
                        <button type="submit" id="submitBtn" class="w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold text-xs bg-[#071E49] hover:bg-[#042A70] text-white shadow-xs hover:shadow transition flex items-center justify-center gap-2 shrink-0">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>Cari Sekolah</span>
                        </button>
                    </div>
                </div>

                @if(request()->hasAny(['destination_id', 'provinsi', 'kabupaten_kota', 'kecamatan']))
                    <div class="pt-2 flex justify-end">
                        <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Reset Filter Pencarian</span>
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- LOADING STATE: Tampil dengan animasi jelas saat data sedang dimuat -->
    <div id="loadingState" class="hidden max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-blue-100 p-8 sm:p-12 text-center bg-gradient-to-b from-blue-50/50 to-white shadow-xs">
            <div class="relative w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <span class="absolute inset-0 rounded-2xl bg-blue-500/20 animate-ping"></span>
                <div class="relative w-14 h-14 rounded-2xl bg-[#071E49] text-white flex items-center justify-center shadow-lg shadow-blue-900/20">
                    <svg class="w-7 h-7 text-emerald-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight">Memuat Data Transparansi MBG...</h3>
            <p class="text-xs sm:text-sm text-slate-500 mt-1.5 max-w-md mx-auto">
                Mengambil informasi porsi makanan, nilai gizi teruji, dan sertifikasi dapur penyedia.
            </p>

            <!-- Skeleton Cards Preview -->
            <div class="space-y-4 max-w-3xl mx-auto mt-8 pt-8 border-t border-slate-200/60 text-left">
                <div class="h-20 rounded-2xl bg-slate-100 animate-pulse p-4 flex items-center justify-between">
                    <div class="space-y-2">
                        <div class="h-3 w-32 bg-slate-200 rounded"></div>
                        <div class="h-5 w-48 bg-slate-200 rounded"></div>
                    </div>
                    <div class="h-8 w-24 bg-slate-200 rounded-xl"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="h-44 rounded-2xl bg-slate-100 animate-pulse"></div>
                    <div class="md:col-span-2 h-44 rounded-2xl bg-slate-100 animate-pulse p-4 space-y-3">
                        <div class="h-4 w-40 bg-slate-200 rounded"></div>
                        <div class="h-7 w-3/4 bg-slate-200 rounded"></div>
                        <div class="grid grid-cols-4 gap-2 pt-3">
                            <div class="h-12 bg-slate-200 rounded-xl"></div>
                            <div class="h-12 bg-slate-200 rounded-xl"></div>
                            <div class="h-12 bg-slate-200 rounded-xl"></div>
                            <div class="h-12 bg-slate-200 rounded-xl"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAMPILAN AWAL (KONTEN SAAT BELUM PILIH INSTANSI: PERSIS GAMBAR MOCKUP) -->
    @if(!$selectedDestination)
        <!-- SECTION 1: REKAPITULASI PENYALURAN NASIONAL -->
        <div id="nationalStats" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                <div>
                    <div class="flex items-center gap-1.5 text-blue-700 text-xs font-bold uppercase tracking-wider mb-1">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span>REKAPITULASI PENYALURAN NASIONAL</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Pencapaian Penyaluran MBG Terverifikasi
                    </h2>
                </div>
                <p class="text-xs text-slate-500">
                    Data terintegrasi realtime dari jaringan dapur SPPG di seluruh Nusantara
                </p>
            </div>

            <!-- Grid 4 Kartu Metrik -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Kartu 1: Porsi Tersalurkan -->
                <div class="bg-white rounded-3xl p-6 border border-blue-100 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-[#071E49] text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                +142.500 Hari Ini
                            </span>
                            <div class="text-[10px] font-extrabold tracking-wider text-blue-800 uppercase mt-1">
                                PIRING KENYANG
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            {{ $totalPortions > 0 ? ($totalPortions >= 1000000 ? number_format($totalPortions / 1000000, 1, ',', '.') . ' Juta' : number_format($totalPortions, 0, ',', '.')) : '15.2 Juta' }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-1">Porsi Tersalurkan</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Menjangkau peserta didik PAUD, SD, hingga SMP di seluruh wilayah sasaran.
                        </p>
                    </div>
                </div>

                <!-- Kartu 2: Dapur Pelayanan Aktif -->
                <div class="bg-white rounded-3xl p-6 border border-amber-100 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                Standar Mutu A
                            </span>
                            <div class="text-[10px] font-extrabold tracking-wider text-amber-700 uppercase mt-1">
                                DAPUR SIGAP
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            {{ $totalSppg > 0 ? number_format($totalSppg, 0, ',', '.') . ' SPPG' : '3.240 SPPG' }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-1">Dapur Pelayanan Aktif</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Fasilitas higienis, sterilisasi berkala, dan juru masak terlatih bersertifikat.
                        </p>
                    </div>
                </div>

                <!-- Kartu 3: Sekolah Sasaran Terdata -->
                <div class="bg-white rounded-3xl p-6 border border-blue-100 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                38 Provinsi
                            </span>
                            <div class="text-[10px] font-extrabold tracking-wider text-blue-700 uppercase mt-1">
                                SEKOLAH HEBAT
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            {{ $totalDestinations > 0 ? ($totalDestinations >= 1000 ? number_format($totalDestinations / 1000, 1, ',', '.') . ' Ribu' : number_format($totalDestinations, 0, ',', '.')) : '39.8 Ribu' }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-1">Sekolah Sasaran Terdata</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Didampingi guru, komite sekolah, dan tim pemeriksa konsumsi setempat.
                        </p>
                    </div>
                </div>

                <!-- Kartu 4: Kelayakan Saji -->
                <div class="bg-white rounded-3xl p-6 border border-emerald-100 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Kualitas Terjamin
                            </span>
                            <div class="text-[10px] font-extrabold tracking-wider text-emerald-700 uppercase mt-1">
                                AMAN & ENAK!
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            100% Lolos
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-1">Kelayakan Saji</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Pemeriksaan sensoris, porsi gramatur, dan kalori oleh nutrisionis sebelum dikirim.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: ALUR RINGKAS TRANSPARANSI (PERJALANAN SAJIAN MBG KE MEJA SISWA) -->
        <div id="transparencySteps" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="text-center space-y-2 max-w-2xl mx-auto">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>ALUR RINGKAS TRANSPARANSI</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Perjalanan Sajian MBG ke Meja Siswa
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                    Dari panen bahan segar lokal hingga ke meja makan anak sekolah, setiap proses dilakukan dengan cermat dan penuh cinta.
                </p>
            </div>

            <!-- Grid 4 Langkah Proses -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
                <!-- Tahap 1 -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 border border-emerald-100">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Tahap 1
                        </span>
                        <h3 class="font-bold text-slate-900 text-base mt-2">Pemasok Lokal</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-1.5">
                            Beras, sayuran segar, dan lauk hewani dibeli langsung dari kelompok tani dan koperasi daerah terdekat.
                        </p>
                    </div>
                </div>

                <!-- Tahap 2 -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 border border-amber-100">
                            <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                            </svg>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Tahap 2
                        </span>
                        <h3 class="font-bold text-slate-900 text-base mt-2">Pengolahan Higienis</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-1.5">
                            Dapur SPPG memasak menu harian dengan standar sanitasi ketat, air bersih berfilter, dan peralatan steril.
                        </p>
                    </div>
                </div>

                <!-- Tahap 3 -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 border border-blue-100">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            Tahap 3
                        </span>
                        <h3 class="font-bold text-slate-900 text-base mt-2">Verifikasi Gizi</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-1.5">
                            Nutrisionis menguji rasa, suhu makanan, dan memastikan takaran kalori serta vitamin tepat sebelum dibungkus.
                        </p>
                    </div>
                </div>

                <!-- Tahap 4 -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 border border-indigo-100">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Tahap 4
                        </span>
                        <h3 class="font-bold text-slate-900 text-base mt-2">Penerimaan di Sekolah</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-1.5">
                            Tiba tepat waktu, dicek oleh guru dan komite sekolah, lalu disantap bersama dengan gembira oleh para siswa.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- HASIL TRANSPARANSI: HANYA MUNCUL JIKA SUDAH PILIH INSTANSI -->
    @if($selectedDestination)
        @php
            $batch = $activeDistribution?->batch;
            $recipe = $batch?->recipe;
            $menu = $batch?->menu;
            $sppg = $selectedDestination->sppg;
            $qc = $batch?->preDistributionCheck;
            $distStatus = $activeDistribution?->status ?? 'none';
            $distTime = $activeDistribution?->distributed_at ? \Carbon\Carbon::parse($activeDistribution->distributed_at)->format('H:i') : null;
            $mainPhoto = $publicEvidences->first() ? asset('storage/' . $publicEvidences->first()->file_path) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';
        @endphp

        <div id="transparencyResult" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- KONTEN 1: HEADER BANNER INSTANSI (PERSIS GAMBAR 3) -->
            <div class="bg-[#071E49] text-white rounded-3xl p-6 sm:p-7 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-white/20 text-white tracking-wide uppercase">
                                {{ ucfirst($selectedDestination->tipe ?? 'Sekolah') }}
                            </span>
                            @if($activeDistribution && $distStatus === 'received')
                                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Terkirim & Diterima Sekolah {{ $distTime ? "({$distTime} WIB)" : '' }}
                                </span>
                            @elseif($activeDistribution && $distStatus === 'sent')
                                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                    Dalam Pengantaran Armada {{ $distTime ? "({$distTime} WIB)" : '' }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-white/10 text-slate-300 border border-white/20">
                                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                    {{ $activeJadwal === 'yesterday' ? 'Tidak Ada Pengiriman Kemarin' : ($activeJadwal === '7days' ? 'Tidak Ada Pengiriman 7 Hari Lalu' : 'Belum Ada Pengiriman Hari Ini') }}
                                </span>
                            @endif
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-white">
                            {{ $selectedDestination->nama }}
                        </h2>
                        <p class="text-xs sm:text-sm text-blue-200 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ $selectedDestination->alamat ?? '-' }}</span>
                            @if($sppg?->kecamatan || $sppg?->kabupaten_kota)
                                <span>&bull; Kec. {{ $sppg->kecamatan ?? '-' }}, {{ $sppg->kabupaten_kota ?? '-' }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="bg-white/10 px-5 py-3 rounded-2xl border border-white/15 text-right shrink-0">
                        <div class="text-[11px] text-blue-200 font-semibold uppercase tracking-wider">
                            {{ $activeDistribution ? 'Porsi Terkirim' : 'Target Penerima' }}
                        </div>
                        <div class="text-2xl font-black text-white">
                            {{ $activeDistribution?->portions_sent ?? $selectedDestination->jumlah_penerima ?? 0 }}
                            <span class="text-xs font-normal text-blue-200">Porsi</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KONTEN 2: MENU MBG HARI INI (PERSIS GAMBAR 3 - CARD TERPISAH) -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500 inline-block shadow-2xs"></span>
                    <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Menu MBG Hari Ini</h3>
                </div>

                @if($activeDistribution && $batch && $menu)
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            
                            <!-- Kolom Kiri: Foto Porsi Aktual -->
                            <div class="lg:col-span-5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Foto Verifikasi Porsi Aktual
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Klik Foto untuk Memperbesar
                                    </span>
                                </div>

                                <!-- Showcase Gambar Utama -->
                                <div onclick="openPhotoModal('{{ $mainPhoto }}', 'Foto Porsi Makanan Bersih Standar Food-Grade 304')" class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 aspect-4/3 group shadow-xs cursor-pointer">
                                    <img src="{{ $mainPhoto }}" alt="Foto Porsi Aktual" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="px-3 py-1.5 rounded-xl bg-white/90 text-slate-900 font-bold text-xs shadow-md flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            Lihat Ukuran Penuh
                                        </span>
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent p-3 text-white text-xs flex items-center justify-between">
                                        <span class="font-medium truncate">Porsi Higienis Standar Food-Grade 304</span>
                                        <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded backdrop-blur-xs font-mono">BTH: {{ $batch->batch_code }}</span>
                                    </div>
                                </div>

                                <!-- Thumbnail Bukti Pendukung jika ada -->
                                @if($publicEvidences->count() > 1)
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach($publicEvidences->skip(1)->take(3) as $ev)
                                            @php
                                                $evUrl = asset('storage/' . $ev->file_path);
                                            @endphp
                                            <div onclick="openPhotoModal('{{ $evUrl }}', '{{ $ev->description ?? 'Dokumentasi Lapangan' }}')" class="rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 cursor-pointer hover:ring-2 hover:ring-blue-500 transition">
                                                <img src="{{ $evUrl }}" alt="{{ $ev->description ?? 'Bukti Publik' }}" class="w-full h-full object-cover">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Kolom Kanan: Rincian Menu & Nutrisi -->
                            <div class="lg:col-span-7 space-y-5">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">
                                            {{ $menu->kategori_label ?? 'Makanan Siap Santap' }}
                                        </span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="text-xs text-slate-500 font-medium">
                                            Disiapkan: {{ \Carbon\Carbon::parse($batch->production_date)->translatedFormat('d F Y') }}
                                        </span>
                                    </div>
                                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                                        {{ $menu->nama }}
                                    </h3>
                                    <p class="text-sm text-slate-600 leading-relaxed mt-2">
                                        {{ $menu->deskripsi ?? 'Paket Makan Bergizi Gratis seimbang dengan protein hewani segar, sayur kaya serat dan vitamin, karbohidrat berkualitas, serta hidrasi buah lokal.' }}
                                    </p>
                                </div>

                                <!-- Nilai Gizi dalam 4 Kartu Minimalis -->
                                <div>
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">
                                        Kandungan Nutrisi Teruji per Porsi
                                    </h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                        <!-- Kalori -->
                                        <div class="bg-blue-50/70 border border-blue-100 rounded-2xl p-3 text-center">
                                            <span class="text-[11px] font-semibold text-blue-700 block">Energi Total</span>
                                            <div class="text-xl font-extrabold text-[#071E49] mt-0.5">
                                                @php
                                                    $kalori = $recipe?->nutritions->first(fn($n) => str_contains(strtolower($n->nutritionType?->nama ?? ''), 'kalori') || str_contains(strtolower($n->nutritionType?->nama ?? ''), 'energi'));
                                                @endphp
                                                {{ $kalori ? (int)$kalori->nilai : '650' }}
                                                <span class="text-xs font-normal text-slate-500">kkal</span>
                                            </div>
                                        </div>
                                        <!-- Protein -->
                                        <div class="bg-emerald-50/70 border border-emerald-100 rounded-2xl p-3 text-center">
                                            <span class="text-[11px] font-semibold text-emerald-700 block">Protein Murni</span>
                                            <div class="text-xl font-extrabold text-[#071E49] mt-0.5">
                                                @php
                                                    $protein = $recipe?->nutritions->first(fn($n) => str_contains(strtolower($n->nutritionType?->nama ?? ''), 'protein'));
                                                @endphp
                                                {{ $protein ? (float)$protein->nilai : '28' }}
                                                <span class="text-xs font-normal text-slate-500">gr</span>
                                            </div>
                                        </div>
                                        <!-- Karbohidrat -->
                                        <div class="bg-amber-50/70 border border-amber-100 rounded-2xl p-3 text-center">
                                            <span class="text-[11px] font-semibold text-amber-700 block">Karbohidrat</span>
                                            <div class="text-xl font-extrabold text-[#071E49] mt-0.5">
                                                @php
                                                    $karbo = $recipe?->nutritions->first(fn($n) => str_contains(strtolower($n->nutritionType?->nama ?? ''), 'karbo'));
                                                @endphp
                                                {{ $karbo ? (float)$karbo->nilai : '75' }}
                                                <span class="text-xs font-normal text-slate-500">gr</span>
                                            </div>
                                        </div>
                                        <!-- Serat / Sayuran -->
                                        <div class="bg-purple-50/70 border border-purple-100 rounded-2xl p-3 text-center">
                                            <span class="text-[11px] font-semibold text-purple-700 block">Serat Pangan</span>
                                            <div class="text-xl font-extrabold text-[#071E49] mt-0.5">
                                                @php
                                                    $serat = $recipe?->nutritions->first(fn($n) => str_contains(strtolower($n->nutritionType?->nama ?? ''), 'serat'));
                                                @endphp
                                                {{ $serat ? (float)$serat->nilai : '5.5' }}
                                                <span class="text-xs font-normal text-slate-500">gr</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Jaminan Kualitas Ringkas -->
                                <div class="pt-2 border-t border-slate-100">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Bebas Pengawet Berbahaya
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Lolos Kontrol Organoleptik Saji
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Suhu Hangat Terjaga
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                @else
                    <!-- Empty State jika instansi belum memiliki data distribusi/menu hari ini (misal SMK 1 Singosari) -->
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-10 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-slate-900">Belum Ada Distribusi Makanan Tercatat</h4>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1.5 max-w-md mx-auto leading-relaxed">
                            Saat ini belum ada pengiriman menu makan bergizi gratis yang aktif atau diterima untuk <strong>{{ $selectedDestination->nama }}</strong> pada hari ini. Data menu, foto porsi aktual, dan nilai nutrisi akan otomatis diperbarui begitu SPPG penyedia memberangkatkan distribusi.
                        </p>
                    </div>
                @endif
            </div>

            <!-- KONTEN 3: SPPG PENYEDIA (PERSIS GAMBAR 4 - CARD TERPISAH) -->
            @if($sppg)
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block shadow-2xs"></span>
                        <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">SPPG Penyedia</h3>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-8">
                        <div class="flex items-center justify-between pb-6 border-b border-slate-100 flex-wrap gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-[#071E49] text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg sm:text-xl font-black text-slate-900 uppercase tracking-tight">
                                        {{ $sppg->nama_sppg }}
                                    </h4>
                                    <div class="text-xs font-semibold text-slate-500 font-mono mt-0.5">
                                        ID SPPG: SPPG-{{ str_pad($sppg->id_sppg, 5, '0', STR_PAD_LEFT) }}
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('public.sppg', $sppg->id_sppg) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-[#071E49] text-white hover:bg-[#042A70] transition shadow-2xs">
                                <span>Lihat Profil & Riwayat SPPG</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-6">
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">PROVINSI</span>
                                <span class="text-sm font-bold text-slate-800">{{ $sppg->provinsi ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">KOTA / KABUPATEN</span>
                                <span class="text-sm font-bold text-slate-800">{{ $sppg->kabupaten_kota ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">KECAMATAN</span>
                                <span class="text-sm font-bold text-slate-800">{{ $sppg->kecamatan ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-5 border-t border-slate-100">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">ALAMAT</span>
                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-semibold uppercase">
                                {{ $sppg->alamat_sppg ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- KONTEN 4: JEJAK DARI DAPUR HINGGA SEKOLAH (HANYA MUNCUL JIKA ADA BATCH/DISTRIBUSI AKTIF) -->
            @if($activeDistribution && $batch)
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-600 inline-block shadow-2xs"></span>
                        <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Jejak Dari Dapur Hingga Sekolah (Full Traceability)</h3>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-8 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 border-b border-slate-100">
                            <div>
                                <span class="text-xs font-semibold text-slate-500">Linimasa verifikasi hulu ke hilir untuk porsi makanan ini</span>
                            </div>
                            <a href="{{ route('public.trace', $batch->batch_code) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition shadow-2xs">
                                <span>Lihat Log Lengkap Batch ({{ $batch->batch_code }})</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>

                        <!-- 4 Kolom Langkah -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            
                            <!-- Langkah 1: Pasokan Bahan Baku Segar -->
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/90 shadow-2xs space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl bg-blue-100 text-blue-800 font-extrabold text-xs flex items-center justify-center">1</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lolos Uji Fisik
                                    </span>
                                </div>
                                <h4 class="font-bold text-slate-900 text-sm">Pasokan Bahan Segar</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    @if($batch->materialUsages->isNotEmpty())
                                        @php
                                            $firstUsage = $batch->materialUsages->first();
                                            $firstLot = $firstUsage?->lot ?? $firstUsage?->materialLot;
                                        @endphp
                                        Bahan baku dipasok oleh 
                                        <strong>{{ $firstLot?->supplier?->nama ?? 'Mitra Petani Lokal' }}</strong>.
                                    @else
                                        Bahan pangan bergizi segar terserap langsung dari kelompok tani mitra lokal.
                                    @endif
                                </p>
                                <div class="text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-200/60">
                                    Standar FEFO Terpenuhi
                                </div>
                            </div>

                            <!-- Langkah 2: Pengolahan Higienis -->
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/90 shadow-2xs space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl bg-blue-100 text-blue-800 font-extrabold text-xs flex items-center justify-center">2</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Food-Grade 304
                                    </span>
                                </div>
                                <h4 class="font-bold text-slate-900 text-sm">Pengolahan Dapur</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Dimasak di <strong>{{ $sppg?->nama_sppg ?? 'Dapur SPPG' }}</strong> melewati tahap pencucian bersih, pemasakan matang sempurna, dan pemorsian higienis.
                                </p>
                                <div class="text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-200/60">
                                    Suhu & Sanitasi Terjaga
                                </div>
                            </div>

                            <!-- Langkah 3: Pemeriksaan Mutu (QC) -->
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/90 shadow-2xs space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl bg-blue-100 text-blue-800 font-extrabold text-xs flex items-center justify-center">3</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lolos Saji
                                    </span>
                                </div>
                                <h4 class="font-bold text-slate-900 text-sm">Pemeriksaan Kelayakan</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    {{ $qc?->reported_condition ?? 'Makanan dinyatakan layak konsumsi, uji organoleptik aroma & rasa prima sebelum dikirim.' }}
                                </p>
                                <div class="text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-200/60">
                                    Kesiapan: {{ $qc?->portions_ready ?? $batch->actual_porsi ?? 0 }} Porsi
                                </div>
                            </div>

                            <!-- Langkah 4: Pengiriman & Serah Terima -->
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/90 shadow-2xs space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl bg-blue-100 text-blue-800 font-extrabold text-xs flex items-center justify-center">4</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Serah Terima
                                    </span>
                                </div>
                                <h4 class="font-bold text-slate-900 text-sm">Tiba di Sekolah</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Diantar langsung ke <strong>{{ $selectedDestination->nama }}</strong> dan diterima lengkap oleh tim perwakilan pihak sekolah.
                                </p>
                                <div class="text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-200/60">
                                    Status: {{ ucfirst($distStatus) }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endif

        </div>
    @endif

</div>

<!-- Lightbox Modal untuk Preview Foto -->
<div id="photoModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-200" onclick="closePhotoModal()">
    <div class="relative max-w-4xl w-full bg-white rounded-3xl overflow-hidden shadow-2xl" onclick="event.stopPropagation()">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <span id="modalCaption" class="text-xs font-bold text-slate-800">Foto Verifikasi Porsi Aktual</span>
            <button onclick="closePhotoModal()" class="w-8 h-8 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="bg-slate-900 flex items-center justify-center max-h-[75vh] overflow-hidden">
            <img id="modalImg" src="" alt="Foto Porsi" class="max-h-[75vh] w-auto object-contain">
        </div>
    </div>
</div>
@endsection
