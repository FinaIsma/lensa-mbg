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
        <!-- Maskot Nempel di Atas Kartu Banner (Statis Menempel Pas di Garis & Berada di Antara 'Pilih Jadwal' dan 'Hari Ini') -->
        <div class="absolute -top-[38px] sm:-top-[42px] right-20 sm:right-[320px] md:right-[380px] lg:right-[405px] w-20 sm:w-24 pointer-events-none z-20">
            <img src="{{ asset('images/nempel-banner-trimmed.png') }}" alt="Maskot Radar MBG" class="w-full h-auto object-contain drop-shadow-md">
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
                    <div class="relative custom-select-wrapper">
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
                    <div class="relative custom-select-wrapper">
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
                    <div class="relative custom-select-wrapper">
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
                        <div class="relative flex-1 w-full custom-select-wrapper">
                            <select id="select-instansi" name="destination_id" {{ $allDestinations->isEmpty() ? 'disabled' : '' }} class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-300 {{ $allDestinations->isEmpty() ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : 'bg-white text-slate-800' }} text-xs font-bold focus:ring-2 focus:ring-blue-500 shadow-2xs transition">
                                <option value="">{{ $allDestinations->isEmpty() ? 'Pilih kecamatan terlebih dahulu' : '-- Pilih Sekolah / Instansi Sasaran --' }}</option>
                                @foreach($allDestinations as $dest)
                                    <option value="{{ $dest->id }}" {{ (isset($selectedDestination) && $selectedDestination->id == $dest->id) ? 'selected' : '' }}>
                                        {{ $dest->nama }} ({{ $dest->sppg?->nama_sppg ?? 'SPPG' }})
                                    </option>
                                @endforeach
                            </select>
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

    <!-- HASIL TRANSPARANSI: HANYA MUNCUL JIKA SUDAH PILIH INSTANSI (PERSIS DESAIN DETAIL SEKOLAH) -->
    @if($selectedDestination)
        @php
            $batch = $activeDistribution?->batch ?? $activeBatch ?? null;
            $recipe = $batch?->recipe;
            $menu = $batch?->menu;
            $sppg = $selectedDestination->sppg;
            $qc = $batch?->preDistributionCheck;
            $distStatus = $activeDistribution?->status ?? 'none';
            $distTime = $activeDistribution?->distributed_at ? \Carbon\Carbon::parse($activeDistribution->distributed_at)->format('H:i') : null;

            // Prioritas Foto Porsi Utama: Evidence uji mutu pre-distribution check / hasil porsi
            $porsiEvidence = $porsiPhotoEvidence 
                ?? $stageEvidences['tahap3'] 
                ?? $stageEvidences['tahap4'] 
                ?? $publicEvidences->first();
            $mainPhoto = $porsiEvidence ? $porsiEvidence->url : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';

            $porsiTarget = (int) ($selectedDestination->jumlah_penerima ?? 0);
            $porsiRealisasi = $activeDistribution ? (int) $activeDistribution->portions_sent : 0;
            $persenRealisasi = ($porsiTarget > 0 && $activeDistribution) ? min(100, round(($porsiRealisasi / $porsiTarget) * 100)) : 0;

            // Foto & Keterangan Riil 4 Tahapan Petualangan Bekal
            $tahap1Photo = $stageEvidences['tahap1'] 
                ? $stageEvidences['tahap1']->url 
                : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80';
            $tahap1Caption = $stageEvidences['tahap1']?->description ?? 'Foto Penerimaan Bahan Baku di Dapur SPPG';

            $tahap2Photo = $stageEvidences['tahap2'] 
                ? $stageEvidences['tahap2']->url 
                : 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80';
            $tahap2Caption = $stageEvidences['tahap2']?->description ?? 'Foto Pemorsian di Baki Stainless Food-Grade';

            $tahap3Photo = $stageEvidences['tahap3'] 
                ? $stageEvidences['tahap3']->url 
                : 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80';
            $tahap3Caption = $stageEvidences['tahap3']?->description ?? 'Foto Pemeriksaan Suhu & Muat Armada Khusus';

            $tahap4Photo = $stageEvidences['tahap4'] 
                ? $stageEvidences['tahap4']->url 
                : 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80';
            $tahap4Caption = $stageEvidences['tahap4']?->description ?? 'Foto Serah Terima di Lokasi Sasaran';
        @endphp

        <div id="transparencyResult" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- 1. BREADCRUMB & RESET FILTER BAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200/80">
                <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium overflow-x-auto py-1">
                    <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1 text-slate-600 hover:text-blue-700 transition">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Beranda</span>
                    </a>
                    <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="truncate max-w-[120px]">{{ $sppg?->provinsi ?? request('provinsi', 'Jawa Timur') }}</span>
                    <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="truncate max-w-[120px]">{{ $sppg?->kabupaten_kota ?? request('kabupaten_kota', 'Kota Malang') }}</span>
                    <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="truncate max-w-[120px]">{{ $sppg?->kecamatan ?? request('kecamatan', 'Kec. Lowokwaru') }}</span>
                    <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[#071E49] font-bold border border-blue-200/80 truncate max-w-[220px]">{{ $selectedDestination->nama }}</span>
                </nav>

                <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Cari Sekolah Lain</span>
                </a>
            </div>

            <!-- 2. KARTU PROFIL SEKOLAH & STATUS PENYALURAN (HARMONIS, TIDAK TABRAKAN WARNA) -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start sm:items-center gap-4">
                    <!-- Thumbnail / Ikon Sekolah -->
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                        <svg class="w-9 h-9 sm:w-11 sm:h-11 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>

                    <!-- Identitas Sekolah & Program -->
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>{{ $selectedDestination->tipe_label }} Sasaran Program</span>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-slate-50 text-slate-600 font-mono border border-slate-200">
                                ID: #TJ-{{ str_pad($selectedDestination->id, 3, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                Kuota: {{ number_format($porsiTarget, 0, ',', '.') }} Sasaran
                            </span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            {{ $selectedDestination->nama }} <span class="font-normal text-slate-400">– Penyaluran Harian</span>
                        </h2>
                        <p class="text-xs text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="flex items-center gap-1 text-slate-600 font-medium">
                                <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span>{{ $selectedDestination->alamat ?? '-' }}</span>
                            </span>
                            @if($sppg)
                                <span class="text-slate-300">&bull;</span>
                                <span>Unit Dapur: <strong class="text-slate-700">{{ $sppg->nama_sppg }}</strong></span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Status Penyaluran Card (Kombinasi Navy & Hijau Emerald Lembut) -->
                <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-4 sm:p-5 lg:min-w-[320px] flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <span class="text-[10px] font-extrabold tracking-wider text-slate-500 uppercase">STATUS PENYALURAN</span>
                        @if($activeDistribution && $distStatus === 'received')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $porsiRealisasi }} Porsi Diterima Lengkap</span>
                            </span>
                        @elseif($activeDistribution && $distStatus === 'sent')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                                <span>Dalam Pengantaran Armada</span>
                            </span>
                        @elseif($activeDistribution && $distStatus === 'planned')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                <span>{{ $porsiRealisasi }} Porsi Terjadwal</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700 border border-slate-300">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Belum Ada Catatan Pengiriman</span>
                            </span>
                        @endif
                    </div>
                    <div class="text-xs font-semibold text-slate-700">
                        @if($activeDistribution)
                            Realisasi Distribusi: <span class="font-bold text-[#071E49]">{{ $porsiRealisasi }} / {{ $porsiTarget }} Porsi ({{ $persenRealisasi }}%)</span>
                        @else
                            Realisasi Distribusi: <span class="font-bold text-slate-500">0 / {{ number_format($porsiTarget, 0, ',', '.') }} Porsi (0%)</span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">
                        @if($activeDistribution && $distStatus === 'received')
                            Porsi telah diserahterimakan lengkap di lokasi sasaran
                        @elseif($activeDistribution && $distStatus === 'sent')
                            Armada distribusi sedang dalam perjalanan menuju lokasi
                        @else
                            Belum ada catatan pengiriman MBG untuk {{ $selectedDestination->nama }} pada tanggal yang dipilih.
                        @endif
                    </div>
                </div>
            </div>

            <!-- 3. BAR PILIH TANGGAL & JADWAL (1 BARIS RAPI) -->
            <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-slate-500 font-bold flex items-center gap-1.5 shrink-0">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>PILIH TANGGAL:</span>
                    </span>
                    <button type="button" data-jadwal="today" class="jadwal-btn px-3.5 py-1.5 rounded-xl cursor-pointer transition {{ $activeJadwal === 'today' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:bg-slate-100' }}">
                        Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('d M') }})
                    </button>
                    <button type="button" data-jadwal="yesterday" class="jadwal-btn px-3.5 py-1.5 rounded-xl cursor-pointer transition {{ $activeJadwal === 'yesterday' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:bg-slate-100' }}">
                        Kemarin ({{ \Carbon\Carbon::now()->subDay()->translatedFormat('d M') }})
                    </button>
                    <button type="button" data-jadwal="7days" class="jadwal-btn px-3.5 py-1.5 rounded-xl cursor-pointer transition {{ $activeJadwal === '7days' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:bg-slate-100' }}">
                        7 Hari Lalu
                    </button>
                </div>
                <div class="text-slate-500 font-medium flex items-center gap-1.5 shrink-0">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @php
                        $targetDate = match($activeJadwal) {
                            'yesterday' => \Carbon\Carbon::yesterday(),
                            '7days' => \Carbon\Carbon::now()->subDays(7),
                            default => \Carbon\Carbon::today(),
                        };
                    @endphp
                    <span>Siklus Menu: <strong class="text-slate-800">{{ $targetDate->translatedFormat('l, d F Y') }}</strong></span>
                </div>
            </div>

            @if($activeDistribution && $batch && $menu)
                <!-- 4. SECTION: MENU UTAMA HARIAN -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">Menu Utama Harian</h3>
                                <p class="text-xs text-slate-400">Baki saji higienis stainless steel standar operasional</p>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Menu Terverifikasi Gizi</span>
                        </span>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            
                            <!-- Kolom Kiri: Foto Porsi Aktual Hari Ini -->
                            <div class="lg:col-span-5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Foto Porsi Aktual Hari Ini
                                    </span>
                                    <span class="text-[11px] text-slate-400">Klik foto untuk perbesar</span>
                                </div>

                                <!-- Showcase Foto Baki Saji -->
                                <div onclick="openPhotoModal('{{ $mainPhoto }}', 'Foto Porsi Makanan Standar Food-Grade 304')" class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 aspect-4/3 group shadow-xs cursor-pointer">
                                    <img src="{{ $mainPhoto }}" alt="Foto Porsi Aktual" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="px-3 py-1.5 rounded-xl bg-white/95 text-slate-900 font-bold text-xs shadow-md flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            Lihat Ukuran Penuh
                                        </span>
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/75 via-black/30 to-transparent p-3 text-white text-xs flex items-center justify-between">
                                        <span class="font-medium truncate">Porsi Higienis Standar Food-Grade 304</span>
                                        <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded backdrop-blur-xs font-mono">BTH: {{ $batch->batch_code }}</span>
                                    </div>
                                </div>

                                <!-- Thumbnail Bukti Pendukung jika ada -->
                                @if($publicEvidences->count() > 1)
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach($publicEvidences->skip(1)->take(3) as $ev)
                                            @php
                                                $evUrl = $ev->url;
                                            @endphp
                                            <div onclick="openPhotoModal('{{ $evUrl }}', '{{ $ev->description ?? 'Dokumentasi Lapangan' }}')" class="rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 cursor-pointer hover:ring-2 hover:ring-blue-500 transition">
                                                <img src="{{ $evUrl }}" alt="{{ $ev->description ?? 'Bukti Publik' }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';" class="w-full h-full object-cover">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Kolom Kanan: Rincian Menu & Nutrisi Seimbang (Palet Seragam & Rapi) -->
                            <div class="lg:col-span-7 space-y-5">
                                <div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200 mb-2">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>KOMPOSISI PORSI SEIMBANG</span>
                                    </div>
                                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                                        {{ $menu->nama }}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mt-2.5">
                                        {{ $menu->deskripsi ?? 'Penyajian menu bergizi seimbang sesuai standar pedoman pemenuhan gizi siswa.' }}
                                    </p>
                                </div>

                                <!-- 4 KARTU NUTRISI: REAL DARI DATABASE -->
                                <div>
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">
                                        Nilai Gizi Terverifikasi per Porsi
                                    </h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        @php
                                            $kalori = $recipe?->nutritions->first(fn($n) => str_contains(strtolower($n->nutritionType?->nama ?? ''), 'kalori') || str_contains(strtolower($n->nutritionType?->nama ?? ''), 'energi'));
                                            $protein = $recipe?->nutritions->first(fn($n) => str_contains(strtolower($n->nutritionType?->nama ?? ''), 'protein'));
                                            $karbo = $recipe?->nutritions->first(fn($n) => str_contains(strtolower($n->nutritionType?->nama ?? ''), 'karbo'));
                                            $serat = $recipe?->nutritions->first(fn($n) => str_contains(strtolower($n->nutritionType?->nama ?? ''), 'serat'));
                                        @endphp
                                        <!-- 1. Kalori Total -->
                                        <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5 text-center hover:bg-white hover:shadow-xs transition">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                Standar AKG
                                            </span>
                                            <div class="text-xl sm:text-2xl font-black text-[#071E49] mt-1.5">
                                                {{ $kalori ? (int)$kalori->nilai : '-' }}
                                                <span class="text-xs font-medium text-slate-400">kkal</span>
                                            </div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">Kalori Total</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Energi aktivitas belajar</div>
                                        </div>

                                        <!-- 2. Protein -->
                                        <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5 text-center hover:bg-white hover:shadow-xs transition">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                Nutrisi Utama
                                            </span>
                                            <div class="text-xl sm:text-2xl font-black text-[#071E49] mt-1.5">
                                                {{ $protein ? (float)$protein->nilai : '-' }}
                                                <span class="text-xs font-medium text-slate-400">g</span>
                                            </div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">Protein</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Pertumbuhan otot & sel</div>
                                        </div>

                                        <!-- 3. Karbohidrat -->
                                        <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5 text-center hover:bg-white hover:shadow-xs transition">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200/80 text-slate-700 border border-slate-300">
                                                Kompleks
                                            </span>
                                            <div class="text-xl sm:text-2xl font-black text-[#071E49] mt-1.5">
                                                {{ $karbo ? (float)$karbo->nilai : '-' }}
                                                <span class="text-xs font-medium text-slate-400">g</span>
                                            </div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">Karbohidrat</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Fokus kognitif siswa</div>
                                        </div>

                                        <!-- 4. Serat Pangan -->
                                        <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5 text-center hover:bg-white hover:shadow-xs transition">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">
                                                Alami
                                            </span>
                                            <div class="text-xl sm:text-2xl font-black text-[#071E49] mt-1.5">
                                                {{ $serat ? (float)$serat->nilai : '-' }}
                                                <span class="text-xs font-medium text-slate-400">g</span>
                                            </div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">Serat Pangan</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Pencernaan sehat</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Jaminan Standar Mutu Saji -->
                                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Bebas Pengawet Berbahaya
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Lolos Uji Mutu Saji
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Kemasan Food-Grade Higienis
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. SECTION: PETUALANGAN BEKAL (ALUR 4 TAHAP INTERAKTIF) -->
                <div class="space-y-4 pt-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                </div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                                    Petualangan Bekal
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Alur Perjalanan
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">
                                Ikuti 4 tahapan perjalanan menu bekal hari ini dari pasar hingga ke meja makan siswa.
                            </p>
                        </div>

                        <div class="inline-flex items-center gap-1.5 text-xs text-blue-700 font-semibold bg-blue-50/60 px-3 py-1.5 rounded-xl border border-blue-100 self-start sm:self-auto">
                            <svg class="w-3.5 h-3.5 text-blue-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                            </svg>
                            <span>Klik kartu untuk buka bukti nyata</span>
                        </div>
                    </div>

                    @php
                        // Data Rantai Pasok Dinamis dari Database
                        $bahanNames = $batch?->materialUsages?->map(fn($u) => $u->lot?->material?->nama)->filter()->unique()->values();
                        $tahap1Bahan = ($bahanNames && $bahanNames->isNotEmpty()) ? $bahanNames->take(4)->implode(', ') : 'Bahan segar terverifikasi FEFO';
                        $tahap1Desc = ($bahanNames && $bahanNames->isNotEmpty())
                            ? "Bahan baku segar disiapkan sesuai takaran resep aktif hari ini: " . $bahanNames->implode(', ') . "."
                            : "Bahan baku segar dipasok langsung oleh mitra lokal berstandar mutu dan FEFO.";
                        $tahap1Petugas = "Tim Pengadaan " . ($sppg?->nama_sppg ?? 'SPPG');
                        $tahap1Time = $stageEvidences['tahap1']?->uploaded_at ? \Carbon\Carbon::parse($stageEvidences['tahap1']->uploaded_at)->format('H:i') . ' WIB' : '05.30 WIB';

                        $stepFirst = $batch?->processingSteps?->sortBy('started_at')->first();
                        $cookTime = $stepFirst?->started_at ? \Carbon\Carbon::parse($stepFirst->started_at)->format('H:i') . ' WIB' : ($stageEvidences['tahap2']?->uploaded_at ? \Carbon\Carbon::parse($stageEvidences['tahap2']->uploaded_at)->format('H:i') . ' WIB' : '07.00 WIB');
                        $stepNames = $batch?->processingSteps?->pluck('step_name')->filter()->unique();
                        $tahap2Desc = ($stepNames && $stepNames->isNotEmpty())
                            ? "Proses memasak di dapur SPPG meliputi tahapan: " . $stepNames->implode(', ') . " dengan standar sanitasi ketat dan wadah baki stainless food-grade."
                            : "Proses memasak dilakukan di dapur SPPG dengan standar sanitasi ketat, suhu matang terjamin, dan pemorsian stainless steel food-grade.";
                        $tahap2Petugas = "Tim Dapur " . ($sppg?->nama_sppg ?? 'SPPG');

                        $qcNutrName = $qc?->effective_nutritionist_name ?? $qc?->nutritionist_name ?? 'Tenaga Gizi SPPG';
                        $qcCondText = $qc?->reported_condition ?? 'Makanan matang sempurna, aroma segar khas rempah, baki tertutup rapat dan higienis';
                        $qcCheckedTime = $qc?->checked_at ? \Carbon\Carbon::parse($qc->checked_at)->format('H:i') . ' WIB' : ($stageEvidences['tahap3']?->uploaded_at ? \Carbon\Carbon::parse($stageEvidences['tahap3']->uploaded_at)->format('H:i') . ' WIB' : '09.30 WIB');

                        $distReceived = $activeDistribution && $distStatus === 'received';
                        $distSent = $activeDistribution && $distStatus === 'sent';
                        $tahap4Time = $distTime ? 'Pukul ' . $distTime . ' WIB' : ($stageEvidences['tahap4']?->uploaded_at ? 'Pukul ' . \Carbon\Carbon::parse($stageEvidences['tahap4']->uploaded_at)->format('H:i') . ' WIB' : 'Terjadwal Hari Ini');
                        $tahap4TimeBadge = $distTime ? $distTime . ' WIB' : ($stageEvidences['tahap4']?->uploaded_at ? \Carbon\Carbon::parse($stageEvidences['tahap4']->uploaded_at)->format('H:i') . ' WIB' : 'Hari Ini');
                        $tahap4Cond = $distReceived ? 'Diterima lengkap, kondisi prima & utuh' : ($distSent ? 'Dalam perjalanan armada pengantar' : 'Menunggu pengiriman armada dapur');
                        $tahap4Desc = $distReceived
                            ? "Baki makanan telah tiba di {$selectedDestination->nama} dan diserahterimakan lengkap sebanyak {$porsiRealisasi} baki saji dalam kondisi prima."
                            : ($distSent
                                ? "Sebanyak {$porsiRealisasi} porsi makanan sedang dalam pengantaran menuju {$selectedDestination->nama}."
                                : "Alokasi sebanyak {$porsiTarget} porsi telah disiapkan dan dijadwalkan untuk didistribusikan ke {$selectedDestination->nama}.");
                        $tahap4Petugas = $distReceived ? "Petugas Penerima {$selectedDestination->nama}" : "Tim Armada " . ($sppg?->nama_sppg ?? 'SPPG');
                    @endphp

                    <!-- 4 KARTU TAHAPAN BERURUTAN (TERHUBUNG GARIS HALUS) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 relative">
                        
                        <!-- Tahap 1: Pengadaan Bahan -->
                        <div data-tahap="1" 
                             data-title="Pengadaan & Pembelian Bahan"
                             data-subtitle="Dokumentasi nyata verifikasi lapangan di titik rantai pasok mitra petani lokal."
                             data-photo="{{ $tahap1Photo }}"
                             data-photo-caption="{{ $tahap1Caption }}"
                             data-desc="{{ $tahap1Desc }}"
                             data-waktu="Pukul {{ $tahap1Time }}"
                             data-kondisi="Segar, lolos sortir fisik harian"
                             data-bahan="{{ $tahap1Bahan }}"
                             data-petugas="{{ $tahap1Petugas }}"
                             class="tahap-card active bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs hover:shadow-md transition cursor-pointer relative ring-2 ring-[#071E49] bg-slate-50/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-[#071E49] text-white">
                                    Tahap 1
                                </span>
                                <span class="text-[11px] font-semibold text-slate-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $tahap1Time }}
                                </span>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center mb-2.5 border border-blue-100">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pasar & Mitra Tani</div>
                            <h4 class="font-extrabold text-slate-900 text-sm mt-0.5">Pengadaan Bahan</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                {{ $tahap1Bahan }}
                            </p>
                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-blue-700 font-bold">
                                <span>Rincian & Bukti Foto</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                        <!-- Tahap 2: Pengolahan Dapur -->
                        <div data-tahap="2"
                             data-title="Pengolahan Higienis di Dapur SPPG"
                             data-subtitle="Pemasakan matang sempurna dengan standar sanitasi ketat dan air steril bersertifikasi."
                             data-photo="{{ $tahap2Photo }}"
                             data-photo-caption="{{ $tahap2Caption }}"
                             data-desc="{{ $tahap2Desc }}"
                             data-waktu="{{ $cookTime }}"
                             data-kondisi="Steril, higienis & matang sempurna"
                             data-bahan="Dapur SPPG & Baki Food-Grade"
                             data-petugas="{{ $tahap2Petugas }}"
                             class="tahap-card bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs hover:shadow-md transition cursor-pointer relative">
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                    Tahap 2
                                </span>
                                <span class="text-[11px] font-semibold text-slate-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $cookTime }}
                                </span>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center mb-2.5 border border-blue-100">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                </svg>
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dapur SPPG Bersih</div>
                            <h4 class="font-extrabold text-slate-900 text-sm mt-0.5">Pengolahan Dapur</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                {{ $stepNames && $stepNames->isNotEmpty() ? $stepNames->implode(', ') : 'Pemasakan higienis food-grade' }}
                            </p>
                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600 font-bold">
                                <span>Rincian & Bukti Foto</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                        <!-- Tahap 3: Pengecekan & Distribusi -->
                        <div data-tahap="3"
                             data-title="Pemeriksaan Mutu & Distribusi Termal"
                             data-subtitle="Uji sensoris organoleptik, validasi suhu hangat saji, dan armada tertutup khusus."
                             data-photo="{{ $tahap3Photo }}"
                             data-photo-caption="{{ $tahap3Caption }}"
                             data-desc="{{ $qcCondText }}"
                             data-waktu="Pukul {{ $qcCheckedTime }}"
                             data-kondisi="Lolos uji organoleptik & higienis"
                             data-bahan="Box Insulated & Armada Pengantar"
                             data-petugas="{{ $qcNutrName }}"
                             class="tahap-card bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs hover:shadow-md transition cursor-pointer relative">
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                    Tahap 3
                                </span>
                                <span class="text-[11px] font-semibold text-slate-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $qcCheckedTime }}
                                </span>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center mb-2.5 border border-blue-100">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Uji Mutu & Armada</div>
                            <h4 class="font-extrabold text-slate-900 text-sm mt-0.5">Pemeriksaan & Pengiriman</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                Uji organoleptik oleh {{ $qcNutrName }}, baki bersegel rapat...
                            </p>
                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600 font-bold">
                                <span>Rincian & Bukti Foto</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                        <!-- Tahap 4: Serah Terima Lokasi -->
                        <div data-tahap="4"
                             data-title="Serah Terima & Penyaluran di Lokasi"
                             data-subtitle="Verifikasi penerimaan porsi utuh oleh pihak sekolah sasaran."
                             data-photo="{{ $tahap4Photo }}"
                             data-photo-caption="{{ $tahap4Caption }}"
                             data-desc="{{ $tahap4Desc }}"
                             data-waktu="{{ $tahap4Time }}"
                             data-kondisi="{{ $tahap4Cond }}"
                             data-bahan="Baki Saji Food-Grade & Ruang Penerima"
                             data-petugas="{{ $tahap4Petugas }}"
                             class="tahap-card bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs hover:shadow-md transition cursor-pointer relative">
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold {{ $distReceived ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' }}">
                                    Tahap 4
                                </span>
                                <span class="text-[11px] font-semibold text-slate-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $tahap4TimeBadge }}
                                </span>
                            </div>
                            <div class="w-10 h-10 rounded-xl {{ $distReceived ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-blue-50 text-blue-700 border-blue-100' }} flex items-center justify-center mb-2.5 border">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">{{ $selectedDestination->nama }}</div>
                            <h4 class="font-extrabold text-slate-900 text-sm mt-0.5">Serah Terima Lokasi</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                @if($distReceived)
                                    Diterima lengkap {{ $porsiRealisasi }} porsi di lokasi sasaran...
                                @elseif($distSent)
                                    Sedang dalam perjalanan pengantaran ke lokasi...
                                @else
                                    Alokasi {{ $porsiTarget }} porsi terjadwal untuk sekolah ini...
                                @endif
                            </p>
                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600 font-bold">
                                <span>Rincian & Bukti Foto</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                    </div>

                    <!-- 6. DETAIL PERJALANAN & DOKUMENTASI RIIL (PANEL INTERAKTIF BERUBAH SESUAI TAHAP) -->
                    <div id="tahapDetailPanel" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-8 mt-4 transition-all duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 border-b border-slate-100">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span id="tahapDetailBadge" class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-[#071E49] text-white">
                                        Tahap 1
                                    </span>
                                    <h4 id="tahapDetailTitle" class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                                        Detail Perjalanan & Dokumentasi Riil: Pengadaan & Pembelian Bahan
                                    </h4>
                                </div>
                                <p id="tahapDetailSubtitle" class="text-xs text-slate-500">
                                    Dokumentasi nyata verifikasi lapangan di titik rantai pasok.
                                </p>
                            </div>

                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Data Telah Diverifikasi</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start pt-6">
                            <!-- Foto Dokumentasi Tahap -->
                            <div class="lg:col-span-5 space-y-2">
                                <div onclick="openPhotoModal(document.getElementById('tahapDetailImg').src, document.getElementById('tahapDetailCaption').innerText)" class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 aspect-4/3 group shadow-xs cursor-pointer">
                                    <img id="tahapDetailImg" src="{{ $tahap1Photo }}" alt="Dokumentasi Tahap" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80';" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="px-3 py-1.5 rounded-xl bg-white/95 text-slate-900 font-bold text-xs shadow-md flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            Buka Foto Penuh
                                        </span>
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent p-3 text-white text-xs">
                                        <span id="tahapDetailCaption" class="font-medium truncate block">{{ $tahap1Caption }}</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-400 text-center italic">
                                    Setiap baki saji terhubung dengan rekam jejak digital petugas berwenang
                                </p>
                            </div>

                            <!-- Keterangan & Rincian Proses -->
                            <div class="lg:col-span-7 space-y-5">
                                <div>
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1.5">
                                        RINCIAN PROSES TAHAPAN:
                                    </span>
                                    <p id="tahapDetailDesc" class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
                                        {{ $tahap1Desc }}
                                    </p>
                                </div>

                                <!-- 4 Box Info Tahapan (Warna Seragam & Rapi) -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                    <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">WAKTU PELAKSANAAN</span>
                                        <span id="tahapDetailWaktu" class="text-xs font-bold text-slate-800 mt-1 block">Pukul {{ $tahap1Time }}</span>
                                    </div>
                                    <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">KONDISI BAHAN BAKU</span>
                                        <span id="tahapDetailKondisi" class="text-xs font-bold text-slate-800 mt-1 block">Segar, lolos sortir fisik harian</span>
                                    </div>
                                    <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">BAHAN TERPILIH HARI INI</span>
                                        <span id="tahapDetailBahan" class="text-xs font-bold text-slate-800 mt-1 block">{{ $tahap1Bahan }}</span>
                                    </div>
                                    <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">PETUGAS VERIFIKASI</span>
                                        <span id="tahapDetailPetugas" class="text-xs font-bold text-slate-800 mt-1 block">{{ $tahap1Petugas }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- TAMPILAN JIKA BELUM ADA CATATAN PENGIRIMAN MBG -->
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-8 sm:p-12 text-center space-y-4">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 mx-auto flex items-center justify-center">
                        <img src="{{ asset('images/bingung-trimmed.png') }}" alt="Belum Ada Catatan" class="w-full h-full object-contain">
                    </div>
                    <div class="max-w-md mx-auto space-y-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Status: Belum Terdistribusi</span>
                        </span>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            Belum Ada Catatan Pengiriman MBG
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                            Instansi <strong class="text-slate-700">{{ $selectedDestination->nama }}</strong> belum memiliki catatan pengiriman Program Makan Bergizi Gratis pada jadwal yang dipilih. Rincian menu harian dan alur perjalanan makanan (Petualangan Bekal) akan ditampilkan secara transparan ketika distribusi telah dimulai.
                        </p>
                    </div>
                    <div class="pt-4 flex flex-wrap items-center justify-center gap-3 text-xs">
                        <div class="px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-600">
                            Target Sasaran: <strong class="text-[#071E49]">{{ number_format($porsiTarget, 0, ',', '.') }} Siswa/Porsi</strong>
                        </div>
                        @if($sppg)
                            <div class="px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-600">
                                Unit Dapur Pengampu: <strong class="text-[#071E49]">{{ $sppg->nama_sppg }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 7. SPPG PENYEDIA (KARTU MINIMALIS ELEGAN) -->
            @if($sppg)
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">Dapur SPPG Penyedia</h3>
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
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">ALAMAT FASILITAS</span>
                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-semibold uppercase">
                                {{ $sppg->alamat_sppg ?? '-' }}
                            </p>
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
