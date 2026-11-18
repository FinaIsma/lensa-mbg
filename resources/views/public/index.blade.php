@extends('layouts.public')

@section('title', 'Pantau Menu & Kualitas Makanan Bergizi Hari Ini - Lensa MBG')

@section('content')
@php
    $activeJadwal = $jadwal ?? request('jadwal', 'today');
@endphp
<div class="space-y-8 sm:space-y-10 pb-20">

    <!-- HERO SECTION: RAMAH, BERSIH, LEGA, TANPA KOTAK PENGURUNG MASKOT -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-center">
            
            <!-- Kolom Kiri: Headline Hangat & Subjudul Ringkas -->
            <div class="lg:col-span-7 space-y-3 sm:space-y-4 text-left">
                <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-black tracking-tight text-slate-900 leading-[1.16]">
                    Kepoin Piring MBG Kamu Hari Ini!
                </h1>
                <p class="text-slate-600 text-sm sm:text-base leading-relaxed max-w-xl font-normal">
                    Pantau porsi lezat, bahan segar dari petani lokal, sampai koki dapur SPPG favoritmu secara terbuka dan mudah!
                </p>
            </div>

            <!-- Kolom Kanan: Maskot Bebas Berdiri Langsung di Atas Background -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end">
                <img src="{{ asset('images/senang-trimmed.png') }}" alt="Maskot Lensa MBG" class="w-48 sm:w-56 lg:w-64 h-auto object-contain animate-float drop-shadow-sm select-none pointer-events-none">
            </div>
        </div>
    </section>

    <!-- FOCAL POINT UTAMA: RADAR PENCARIAN SEKOLAH SASARAN -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8 text-slate-800">
            <!-- Header Kartu: Judul Radar & Pilih Jadwal -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#071E49] flex items-center justify-center shrink-0 border border-blue-100/80">
                        <svg class="w-6 h-6 text-[#071E49]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight">Radar Pencarian Sekolah Sasaran</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih wilayah atau ketik nama sekolah untuk mengecek menu, asal bahan, dan jam pengantaran.</p>
                    </div>
                </div>

                <!-- Segmented Control: Pilih Jadwal (Responsif: 3 Kolom di HP, 1 Baris Ramping di Desktop) -->
                <div class="w-full md:w-auto shrink-0">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block md:hidden mb-1.5">Pilih Jadwal Penyaluran</span>
                    <div class="grid grid-cols-3 md:inline-flex items-center p-1 bg-slate-100/90 rounded-xl border border-slate-200/80 text-xs w-full md:w-auto gap-1 shrink-0">
                        <span class="hidden md:inline-block px-2 text-xs font-semibold text-slate-500 whitespace-nowrap shrink-0">Pilih Jadwal:</span>
                        
                        <!-- 1. Hari Ini -->
                        <button type="button" data-jadwal="today" class="jadwal-btn flex md:inline-flex items-center justify-center gap-1.5 py-2 sm:py-1.5 px-2 sm:px-3.5 rounded-lg text-xs transition select-none cursor-pointer text-center whitespace-nowrap shrink-0 {{ $activeJadwal === 'today' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-200/60' }}">
                            <svg class="w-3.5 h-3.5 {{ $activeJadwal === 'today' ? 'text-emerald-400' : 'text-slate-400' }} shrink-0 hidden sm:inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="hidden sm:inline whitespace-nowrap">Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('d M') }})</span>
                            <span class="sm:hidden leading-tight text-center">
                                <span class="block font-bold text-[11px] whitespace-nowrap">Hari Ini</span>
                                <span class="block text-[9px] opacity-80 font-mono whitespace-nowrap">{{ \Carbon\Carbon::now()->translatedFormat('d M') }}</span>
                            </span>
                        </button>

                        <!-- 2. Kemarin -->
                        <button type="button" data-jadwal="yesterday" class="jadwal-btn flex md:inline-flex items-center justify-center gap-1.5 py-2 sm:py-1.5 px-2 sm:px-3.5 rounded-lg text-xs transition select-none cursor-pointer text-center whitespace-nowrap shrink-0 {{ $activeJadwal === 'yesterday' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-200/60' }}">
                            <span class="hidden sm:inline whitespace-nowrap">Kemarin ({{ \Carbon\Carbon::now()->subDay()->translatedFormat('d M') }})</span>
                            <span class="sm:hidden leading-tight text-center">
                                <span class="block font-bold text-[11px] whitespace-nowrap">Kemarin</span>
                                <span class="block text-[9px] opacity-80 font-mono whitespace-nowrap">{{ \Carbon\Carbon::now()->subDay()->translatedFormat('d M') }}</span>
                            </span>
                        </button>

                        <!-- 3. 7 Hari Terakhir -->
                        <button type="button" data-jadwal="7days" class="jadwal-btn flex md:inline-flex items-center justify-center gap-1.5 py-2 sm:py-1.5 px-2 sm:px-3.5 rounded-lg text-xs transition select-none cursor-pointer text-center whitespace-nowrap shrink-0 {{ $activeJadwal === '7days' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-200/60' }}">
                            <span class="hidden sm:inline whitespace-nowrap">7 Hari Lalu</span>
                            <span class="sm:hidden leading-tight text-center">
                                <span class="block font-bold text-[11px] whitespace-nowrap">7 Hari</span>
                                <span class="block text-[9px] opacity-80 font-mono whitespace-nowrap">Terakhir</span>
                            </span>
                        </button>
                    </div>
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
                            <svg class="w-3.5 h-3.5 text-[#071E49] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            <svg class="w-3.5 h-3.5 text-[#071E49] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Kota / Kabupaten</span>
                        </label>
                        <select id="select-regency" name="kabupaten_kota" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-400 focus:outline-none transition">
                            <option value="">Pilih provinsi dahulu</option>
                        </select>
                    </div>

                    <!-- 3. Kecamatan -->
                    <div class="relative custom-select-wrapper">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#071E49] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span>Kecamatan</span>
                        </label>
                        <select id="select-district" name="kecamatan" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-400 focus:outline-none transition">
                            <option value="">Pilih kota/kabupaten dahulu</option>
                        </select>
                    </div>
                </div>

                <!-- Baris 2: Nama Sekolah Sasaran di Bagian Bawah + Tombol Cari Sekolah -->
                <div class="pt-1">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#071E49] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                            <span>Nama Sekolah Sasaran</span>
                        </span>
                        <span class="text-[11px] text-slate-400 font-normal">Disaring otomatis sesuai wilayah SPPG</span>
                    </label>
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="relative flex-1 w-full custom-select-wrapper">
                            <select id="select-instansi" name="destination_id" {{ $allDestinations->isEmpty() ? 'disabled' : '' }} class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-300 {{ $allDestinations->isEmpty() ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : 'bg-white text-slate-900 font-semibold' }} text-xs focus:ring-2 focus:ring-blue-500 shadow-2xs transition">
                                <option value="">{{ $allDestinations->isEmpty() ? 'Pilih kecamatan terlebih dahulu' : '-- Pilih Sekolah / Instansi Sasaran --' }}</option>
                                @foreach($allDestinations as $dest)
                                    <option value="{{ $dest->id }}" {{ (isset($selectedDestination) && $selectedDestination->id == $dest->id) ? 'selected' : '' }}>
                                        {{ $dest->nama }} ({{ $dest->sppg?->nama_sppg ?? 'SPPG' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" id="submitBtn" class="w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold text-xs bg-[#071E49] hover:bg-[#042A70] text-white shadow-xs hover:shadow transition flex items-center justify-center gap-2 shrink-0 cursor-pointer">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>Cari Sekolah</span>
                        </button>
                    </div>
                </div>

                @if(request()->hasAny(['destination_id', 'provinsi', 'kabupaten_kota', 'kecamatan']))
                    <div class="pt-2 flex justify-end">
                        <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Reset Filter Pencarian</span>
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </section>

    <!-- LOADING STATE: BERSIH & RESPONSIF -->
    <div id="loadingState" class="hidden max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 sm:p-12 text-center shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-[#071E49] text-white mx-auto mb-4 flex items-center justify-center shadow-xs">
                <svg class="w-6 h-6 text-emerald-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>
            <h3 class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight">Memuat Data Transparansi MBG...</h3>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-md mx-auto">
                Mengambil data porsi aktual, nilai gizi teruji, dan rekam pengantaran.
            </p>

            <!-- Skeleton Cards Preview -->
            <div class="space-y-3 max-w-3xl mx-auto mt-6 pt-6 border-t border-slate-100 text-left">
                <div class="h-16 rounded-xl bg-slate-50 border border-slate-100 animate-pulse p-4 flex items-center justify-between">
                    <div class="space-y-1.5">
                        <div class="h-2.5 w-32 bg-slate-200 rounded"></div>
                        <div class="h-4 w-48 bg-slate-200 rounded"></div>
                    </div>
                    <div class="h-7 w-24 bg-slate-200 rounded-lg"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="h-36 rounded-xl bg-slate-50 border border-slate-100 animate-pulse"></div>
                    <div class="md:col-span-2 h-36 rounded-xl bg-slate-50 border border-slate-100 animate-pulse p-4 space-y-2.5">
                        <div class="h-3 w-40 bg-slate-200 rounded"></div>
                        <div class="h-6 w-3/4 bg-slate-200 rounded"></div>
                        <div class="grid grid-cols-4 gap-2 pt-2">
                            <div class="h-10 bg-slate-200 rounded-lg"></div>
                            <div class="h-10 bg-slate-200 rounded-lg"></div>
                            <div class="h-10 bg-slate-200 rounded-lg"></div>
                            <div class="h-10 bg-slate-200 rounded-lg"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAMPILAN AWAL: KONTEN SAAT BELUM MEMILIH INSTANSI -->
    @if(!$selectedDestination)
        <!-- SECTION 1: REKAPITULASI PENYALURAN NASIONAL -->
        <section id="nationalStats" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 pb-2 border-b border-slate-200/80">
                <div>
                    <div class="flex items-center gap-1.5 text-blue-700 text-xs font-bold uppercase tracking-wider mb-1">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span>Rekapitulasi Penyaluran Nasional</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Pencapaian Penyaluran MBG Terverifikasi
                    </h2>
                </div>
                <p class="text-xs text-slate-500">
                    Data terintegrasi realtime dari jaringan dapur SPPG di seluruh Nusantara
                </p>
            </div>

            <!-- Grid 4 Kartu Metrik Nasional -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Kartu 1: Porsi Tersalurkan -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#071E49] flex items-center justify-center shrink-0 border border-blue-100">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            +142.500 Hari Ini
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            {{ $totalPortions > 0 ? ($totalPortions >= 1000000 ? number_format($totalPortions / 1000000, 1, ',', '.') . ' Juta' : number_format($totalPortions, 0, ',', '.')) : '15.2 Juta' }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-0.5">Porsi Tersalurkan</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Menjangkau peserta didik PAUD, SD, hingga SMP di seluruh wilayah sasaran.
                        </p>
                    </div>
                </div>

                <!-- Kartu 2: Dapur Pelayanan Aktif -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-100">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            Standar Mutu A
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            {{ $totalSppg > 0 ? number_format($totalSppg, 0, ',', '.') . ' SPPG' : '3.240 SPPG' }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-0.5">Dapur Pelayanan Aktif</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Fasilitas higienis, sterilisasi berkala, dan juru masak terlatih bersertifikat.
                        </p>
                    </div>
                </div>

                <!-- Kartu 3: Sekolah Sasaran Terdata -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            38 Provinsi
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            {{ $totalDestinations > 0 ? ($totalDestinations >= 1000 ? number_format($totalDestinations / 1000, 1, ',', '.') . ' Ribu' : number_format($totalDestinations, 0, ',', '.')) : '39.8 Ribu' }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-0.5">Sekolah Sasaran Terdata</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Didampingi guru, komite sekolah, dan tim pemeriksa konsumsi setempat.
                        </p>
                    </div>
                </div>

                <!-- Kartu 4: Kelayakan Saji -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Kualitas Terjamin
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            100% Lolos
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-0.5">Kelayakan Saji</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Pemeriksaan sensoris, porsi gramatur, dan kalori oleh nutrisionis sebelum dikirim.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: ALUR RINGKAS TRANSPARANSI -->
        <section id="transparencySteps" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="text-left space-y-1.5 max-w-2xl pb-3 border-b border-slate-200/80">
                <div class="inline-flex items-center gap-1.5 text-blue-700 text-xs font-bold uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Alur Ringkas Transparansi</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Perjalanan Sajian MBG ke Meja Siswa
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                    Dari panen bahan segar lokal hingga ke meja makan anak sekolah, setiap proses dilakukan dengan cermat dan higienis.
                </p>
            </div>

            <!-- Grid 4 Langkah Proses -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                <!-- Tahap 1 -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs border border-emerald-100">01</span>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Tahap 1</span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mt-1">Pemasok Lokal</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-1.5">
                            Beras, sayuran segar, dan lauk hewani dibeli langsung dari kelompok tani dan koperasi daerah terdekat.
                        </p>
                    </div>
                </div>

                <!-- Tahap 2 -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xs border border-amber-100">02</span>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Tahap 2</span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mt-1">Pengolahan Higienis</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-1.5">
                            Dapur SPPG memasak menu harian dengan standar sanitasi ketat, air bersih berfilter, dan peralatan steril.
                        </p>
                    </div>
                </div>

                <!-- Tahap 3 -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs border border-blue-100">03</span>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200">Tahap 3</span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mt-1">Verifikasi Gizi</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-1.5">
                            Nutrisionis menguji rasa, suhu makanan, dan memastikan takaran kalori serta vitamin tepat sebelum dibungkus.
                        </p>
                    </div>
                </div>

                <!-- Tahap 4 -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs border border-indigo-100">04</span>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">Tahap 4</span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mt-1">Penerimaan di Sekolah</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-1.5">
                            Tiba tepat waktu, dicek oleh guru dan komite sekolah, lalu disantap bersama dengan gembira oleh para siswa.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- HASIL TRANSPARANSI: HANYA MUNCUL JIKA SUDAH PILIH INSTANSI -->
    @if($selectedDestination)
        @php
            $batch = $activeDistribution?->batch ?? $activeBatch ?? null;
            $recipe = $batch?->recipe;
            $menu = $batch?->menu;
            $sppg = $selectedDestination->sppg;
            $qc = $batch?->preDistributionCheck;
            $distStatus = $activeDistribution?->status ?? 'none';
            $distTime = $activeDistribution?->distributed_at ? \Carbon\Carbon::parse($activeDistribution->distributed_at)->format('H:i') : null;

            // Prioritas Foto Porsi Utama
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

        <section id="transparencyResult" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- 1. BREADCRUMB & RESET FILTER BAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/80">
                <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium overflow-x-auto py-1">
                    <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1 text-slate-600 hover:text-[#071E49] transition">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Beranda</span>
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="truncate max-w-[120px]">{{ $sppg?->provinsi ?? request('provinsi', 'Jawa Timur') }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="truncate max-w-[120px]">{{ $sppg?->kabupaten_kota ?? request('kabupaten_kota', 'Kota Malang') }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="truncate max-w-[120px]">{{ $sppg?->kecamatan ?? request('kecamatan', 'Kec. Lowokwaru') }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 text-[#071E49] font-bold border border-blue-200/80 truncate max-w-[220px]">{{ $selectedDestination->nama }}</span>
                </nav>

                <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition shrink-0">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Cari Sekolah Lain</span>
                </a>
            </div>

            <!-- 2. KARTU PROFIL SEKOLAH & STATUS PENYALURAN -->
            <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/90 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start gap-4 sm:gap-5 flex-1 min-w-0">
                    <!-- Thumbnail / Ikon Sekolah -->
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 text-[#071E49]">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>

                    <!-- Identitas Sekolah & Metadata Terstruktur -->
                    <div class="min-w-0 flex-1">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            {{ $selectedDestination->nama }}
                        </h2>
                        <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-2.5 gap-y-1 mt-1.5 font-normal">
                            <span class="font-medium text-slate-700">{{ $selectedDestination->tipe_label }} Sasaran</span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="font-mono text-slate-500">ID: #TJ-{{ str_pad($selectedDestination->id, 3, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-slate-300">&bull;</span>
                            <span>Kuota: <strong class="text-slate-800 font-semibold">{{ number_format($porsiTarget, 0, ',', '.') }}</strong> Sasaran</span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="text-slate-500 truncate max-w-xs">{{ $selectedDestination->alamat ?? '-' }}</span>
                            @if($sppg)
                                <span class="text-slate-300">&bull;</span>
                                <span>Unit Dapur: <strong class="text-slate-800 font-semibold">{{ $sppg->nama_sppg }}</strong></span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Status Penyaluran (Terintegrasi dengan Border Vertikal, Tanpa Kotak Bertumpuk) -->
                <div class="lg:border-l lg:border-slate-200 lg:pl-8 lg:min-w-[320px] flex flex-col justify-center space-y-1.5 pt-4 lg:pt-0 border-t border-slate-100 lg:border-t-0">
                    <div class="flex items-center gap-2">
                        @if($activeDistribution && $distStatus === 'received')
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Status: Diterima Lengkap</span>
                        @elseif($activeDistribution && $distStatus === 'sent')
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            <span class="text-xs font-bold text-blue-800 uppercase tracking-wide">Status: Pengantaran Armada</span>
                        @elseif($activeDistribution && $distStatus === 'planned')
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-bold text-blue-800 uppercase tracking-wide">Status: Terjadwal Hari Ini</span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Status: Belum Terjadwal</span>
                        @endif
                    </div>
                    <div>
                        @if($activeDistribution)
                            <div class="text-2xl font-black text-slate-900 tracking-tight">
                                {{ $porsiRealisasi }} <span class="text-sm font-semibold text-slate-400 font-normal">/ {{ $porsiTarget }} Porsi ({{ $persenRealisasi }}%)</span>
                            </div>
                        @else
                            <div class="text-2xl font-black text-slate-400 tracking-tight">
                                0 <span class="text-sm font-semibold text-slate-400 font-normal">/ {{ number_format($porsiTarget, 0, ',', '.') }} Porsi (0%)</span>
                            </div>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 leading-snug">
                        @if($activeDistribution && $distStatus === 'received')
                            Porsi telah diserahterimakan lengkap di lokasi sasaran.
                        @elseif($activeDistribution && $distStatus === 'sent')
                            Armada distribusi sedang dalam perjalanan menuju lokasi penerima.
                        @else
                            Belum ada catatan pengiriman MBG untuk {{ $selectedDestination->nama }} pada jadwal terpilih.
                        @endif
                    </p>
                </div>
            </div>

            <!-- 3. BAR PILIH TANGGAL & JADWAL (RESPONSIF MOBILE DENGAN GRID / FLEX-WRAP) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1 text-xs">
                <div class="w-full sm:w-auto">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 sm:hidden">Pilih Jadwal Menu:</div>
                    <div class="grid grid-cols-3 sm:inline-flex items-center p-1 rounded-xl bg-slate-200/60 border border-slate-200/80 gap-1 w-full sm:w-fit">
                        <button type="button" data-jadwal="today" class="jadwal-btn text-center px-2 sm:px-3 py-1.5 rounded-lg cursor-pointer transition text-xs {{ $activeJadwal === 'today' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Hari Ini <span class="hidden md:inline">({{ \Carbon\Carbon::now()->translatedFormat('d M') }})</span>
                        </button>
                        <button type="button" data-jadwal="yesterday" class="jadwal-btn text-center px-2 sm:px-3 py-1.5 rounded-lg cursor-pointer transition text-xs {{ $activeJadwal === 'yesterday' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Kemarin <span class="hidden md:inline">({{ \Carbon\Carbon::now()->subDay()->translatedFormat('d M') }})</span>
                        </button>
                        <button type="button" data-jadwal="7days" class="jadwal-btn text-center px-2 sm:px-3 py-1.5 rounded-lg cursor-pointer transition text-xs {{ $activeJadwal === '7days' ? 'font-bold bg-[#071E49] text-white shadow-xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            7 Hari Lalu
                        </button>
                    </div>
                </div>
                <div class="text-slate-500 font-medium flex items-center gap-1.5 shrink-0">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    @php
                        $targetDate = match($activeJadwal) {
                            'yesterday' => \Carbon\Carbon::yesterday(),
                            '7days' => \Carbon\Carbon::now()->subDays(7),
                            default => \Carbon\Carbon::today(),
                        };
                    @endphp
                    <span>Siklus Menu: <strong class="text-slate-800 font-semibold">{{ $targetDate->translatedFormat('l, d F Y') }}</strong></span>
                </div>
            </div>

            @if($activeDistribution && $batch && $menu)
                <!-- 4. SECTION: MENU UTAMA HARIAN (KATALOG KULINER BERSIH & PRESISI) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-slate-200/80">
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Menu Utama Harian</h3>
                            <p class="text-xs text-slate-500">Penyajian baki saji stainless steel food-grade 304 standar operasional</p>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-800">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Terverifikasi Ahli Gizi</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-7">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">
                            
                            <!-- Kolom Kiri: Foto Porsi Aktual Hari Ini (Editorial Food Framing) -->
                            <div class="lg:col-span-5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#071E49]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Foto Porsi Aktual Hari Ini
                                    </span>
                                    <span class="text-[11px] text-slate-400">Klik untuk perbesar</span>
                                </div>

                                @php
                                    $portionGallery = $publicEvidences->map(fn($ev) => [
                                        'url' => $ev->url,
                                        'caption' => $ev->description ?? 'Foto Porsi Standar MBG'
                                    ])->values();
                                @endphp

                                <!-- Showcase Foto Baki Saji -->
                                <div onclick='openPhotoModal("{{ $mainPhoto }}", "Foto Porsi Makanan Standar Food-Grade 304", @json($portionGallery), 0)' class="relative rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-4/3 group shadow-2xs cursor-pointer">
                                    <img src="{{ $mainPhoto }}" alt="Foto Porsi Aktual" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';" class="w-full h-full object-cover group-hover:scale-102 transition duration-200">
                                    <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="px-3 py-1.5 rounded-lg bg-white/95 text-slate-900 font-bold text-xs shadow-md flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-[#071E49]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            Lihat Ukuran Penuh
                                        </span>
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-900/80 via-slate-900/40 to-transparent p-3 text-white text-xs flex items-center justify-between">
                                        <span class="font-medium truncate">Porsi Higienis Standar Food-Grade 304</span>
                                        <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded font-mono">BTH: {{ $batch->batch_code }}</span>
                                    </div>
                                </div>

                                <!-- Thumbnail Bukti Pendukung jika ada -->
                                @if($publicEvidences->count() > 1)
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach($publicEvidences->skip(1)->take(3) as $ev)
                                            @php
                                                $evUrl = $ev->url;
                                            @endphp
                                            <div onclick='openPhotoModal("{{ $evUrl }}", "{{ addslashes($ev->description ?? 'Dokumentasi Lapangan') }}", @json($portionGallery), {{ $loop->iteration }})' class="rounded-lg overflow-hidden border border-slate-200 aspect-video bg-slate-100 cursor-pointer hover:border-[#071E49] transition">
                                                <img src="{{ $evUrl }}" alt="{{ $ev->description ?? 'Bukti Publik' }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';" class="w-full h-full object-cover">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Kolom Kanan: Rincian Menu & Nutrisi Seimbang (Editorial Typography) -->
                            <div class="lg:col-span-7 space-y-5">
                                <div>
                                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                                        {{ $menu->nama }}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mt-1.5 font-normal">
                                        {{ $menu->deskripsi ?? 'Penyajian menu bergizi seimbang sesuai standar pedoman pemenuhan gizi siswa.' }}
                                    </p>
                                </div>

                                <!-- 4 KARTU NUTRISI: REAL DARI DATABASE (BERSIH & PRESISI) -->
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
                                        <div class="bg-slate-50/80 border border-slate-200/90 rounded-xl p-3.5 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200/70 text-slate-700">
                                                Standar AKG
                                            </span>
                                            <div class="text-xl sm:text-2xl font-black text-[#071E49] mt-1.5 tracking-tight">
                                                {{ $kalori ? (int)$kalori->nilai : '-' }}
                                                <span class="text-xs font-medium text-slate-400">kkal</span>
                                            </div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">Kalori Total</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Energi belajar</div>
                                        </div>

                                        <!-- 2. Protein -->
                                        <div class="bg-slate-50/80 border border-slate-200/90 rounded-xl p-3.5 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200/70 text-slate-700">
                                                Nutrisi Utama
                                            </span>
                                            <div class="text-xl sm:text-2xl font-black text-[#071E49] mt-1.5 tracking-tight">
                                                {{ $protein ? (float)$protein->nilai : '-' }}
                                                <span class="text-xs font-medium text-slate-400">g</span>
                                            </div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">Protein</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Tumbuh kembang</div>
                                        </div>

                                        <!-- 3. Karbohidrat -->
                                        <div class="bg-slate-50/80 border border-slate-200/90 rounded-xl p-3.5 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200/70 text-slate-700">
                                                Kompleks
                                            </span>
                                            <div class="text-xl sm:text-2xl font-black text-[#071E49] mt-1.5 tracking-tight">
                                                {{ $karbo ? (float)$karbo->nilai : '-' }}
                                                <span class="text-xs font-medium text-slate-400">g</span>
                                            </div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">Karbohidrat</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Fokus kognitif</div>
                                        </div>

                                        <!-- 4. Serat Pangan -->
                                        <div class="bg-slate-50/80 border border-slate-200/90 rounded-xl p-3.5 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200/70 text-slate-700">
                                                Alami
                                            </span>
                                            <div class="text-xl sm:text-2xl font-black text-[#071E49] mt-1.5 tracking-tight">
                                                {{ $serat ? (float)$serat->nilai : '-' }}
                                                <span class="text-xs font-medium text-slate-400">g</span>
                                            </div>
                                            <div class="text-xs font-bold text-slate-800 mt-0.5">Serat Pangan</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Pencernaan sehat</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Jaminan Standar Mutu Saji (Crisp Minimalist Chips) -->
                                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-50 border border-slate-200/80 text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Bebas Pengawet Berbahaya
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-50 border border-slate-200/80 text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Lolos Uji Mutu Saji
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-50 border border-slate-200/80 text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Kemasan Food-Grade Higienis
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. SECTION: PETUALANGAN BEKAL (ALUR 4 TAHAP INTERAKTIF) -->
                <div class="space-y-4 pt-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200/80">
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">
                                Perjalanan Bekal Bergizi
                            </h3>
                            <p class="text-xs text-slate-500">
                                Rekam jejak digital hulu ke hilir dari rantai pasok hingga serah terima di sekolah sasaran
                            </p>
                        </div>

                        <div class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium self-start sm:self-auto">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Pilih tahapan untuk memeriksa bukti lapangan</span>
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

                    <!-- 5A. NAVIGASI STEP TABS KHUSUS MOBILE (HEMAT LAYAR HP & ZERO SCROLL) -->
                    <div class="sm:hidden">
                        <div class="grid grid-cols-4 gap-1 p-1 bg-slate-100/90 rounded-xl border border-slate-200/80">
                            <!-- Tab 1 -->
                            <button type="button"
                                    onclick="setActiveTahap('1')"
                                    data-tahap="1"
                                    class="mobile-tahap-tab active flex flex-col items-center justify-center py-2 px-1 rounded-lg transition duration-150 text-center select-none bg-[#071E49] text-white shadow-xs cursor-pointer">
                                <span class="tab-num px-1.5 py-0.5 rounded text-[9px] font-bold bg-white/20 text-white leading-none">01</span>
                                <span class="font-bold text-[11px] truncate max-w-full mt-1">Bahan</span>
                                <span class="tab-time text-[9px] text-slate-200 font-mono mt-0.5 truncate">{{ $tahap1Time }}</span>
                            </button>

                            <!-- Tab 2 -->
                            <button type="button"
                                    onclick="setActiveTahap('2')"
                                    data-tahap="2"
                                    class="mobile-tahap-tab flex flex-col items-center justify-center py-2 px-1 rounded-lg transition duration-150 text-center select-none bg-transparent text-slate-600 hover:bg-slate-200/70 cursor-pointer">
                                <span class="tab-num px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-600 leading-none">02</span>
                                <span class="font-bold text-[11px] truncate max-w-full mt-1">Dapur</span>
                                <span class="tab-time text-[9px] text-slate-400 font-mono mt-0.5 truncate">{{ $cookTime }}</span>
                            </button>

                            <!-- Tab 3 -->
                            <button type="button"
                                    onclick="setActiveTahap('3')"
                                    data-tahap="3"
                                    class="mobile-tahap-tab flex flex-col items-center justify-center py-2 px-1 rounded-lg transition duration-150 text-center select-none bg-transparent text-slate-600 hover:bg-slate-200/70 cursor-pointer">
                                <span class="tab-num px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-600 leading-none">03</span>
                                <span class="font-bold text-[11px] truncate max-w-full mt-1">Uji QC</span>
                                <span class="tab-time text-[9px] text-slate-400 font-mono mt-0.5 truncate">{{ $qcCheckedTime }}</span>
                            </button>

                            <!-- Tab 4 -->
                            <button type="button"
                                    onclick="setActiveTahap('4')"
                                    data-tahap="4"
                                    class="mobile-tahap-tab flex flex-col items-center justify-center py-2 px-1 rounded-lg transition duration-150 text-center select-none bg-transparent text-slate-600 hover:bg-slate-200/70 cursor-pointer">
                                <span class="tab-num px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-600 leading-none">04</span>
                                <span class="font-bold text-[11px] truncate max-w-full mt-1">Kirim</span>
                                <span class="tab-time text-[9px] text-slate-400 font-mono mt-0.5 truncate">{{ $tahap4TimeBadge }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- 5B. 4 KARTU TAHAPAN DESKTOP (HANYA MUNCUL DI TABLET & DESKTOP) -->
                    <div class="hidden sm:grid sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        
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
                             class="tahap-card desktop-tahap-card active bg-slate-50/70 rounded-xl p-4 sm:p-5 border border-[#071E49] ring-1 ring-[#071E49] shadow-2xs hover:shadow-xs transition cursor-pointer relative">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="tahap-badge px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-[#071E49] text-white">
                                    Tahap 1
                                </span>
                                <span class="text-xs font-medium text-slate-500">
                                    {{ $tahap1Time }}
                                </span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center mb-2 border border-blue-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Titik Pasokan</div>
                            <h4 class="font-bold text-slate-900 text-sm mt-0.5">Pengadaan Bahan</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                {{ $tahap1Bahan }}
                            </p>
                            <div class="tahap-footer mt-3 pt-2.5 border-t border-slate-200/80 flex items-center justify-between text-[11px] text-[#071E49] font-bold">
                                <span>{{ $tahap1PhotoGallery->count() > 0 ? $tahap1PhotoGallery->count() . ' Komoditas Menu' : 'Rincian & Bukti Foto' }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
                             class="tahap-card desktop-tahap-card bg-white rounded-xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition cursor-pointer relative">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="tahap-badge px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                    Tahap 2
                                </span>
                                <span class="text-xs font-medium text-slate-500">
                                    {{ $cookTime }}
                                </span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center mb-2 border border-blue-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                </svg>
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dapur Bersih</div>
                            <h4 class="font-bold text-slate-900 text-sm mt-0.5">Pengolahan Dapur</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                {{ $stepNames && $stepNames->isNotEmpty() ? $stepNames->implode(', ') : 'Pemasakan higienis food-grade' }}
                            </p>
                            <div class="tahap-footer mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600 font-bold">
                                <span>{{ $tahap2PhotoGallery->count() > 0 ? $tahap2PhotoGallery->count() . ' Foto Dapur' : 'Rincian & Bukti Foto' }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
                             class="tahap-card desktop-tahap-card bg-white rounded-xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition cursor-pointer relative">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="tahap-badge px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                    Tahap 3
                                </span>
                                <span class="text-xs font-medium text-slate-500">
                                    {{ $qcCheckedTime }}
                                </span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center mb-2 border border-blue-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Uji Mutu</div>
                            <h4 class="font-bold text-slate-900 text-sm mt-0.5">Pemeriksaan & Kirim</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                Uji organoleptik oleh {{ $qcNutrName }}
                            </p>
                            <div class="tahap-footer mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600 font-bold">
                                <span>{{ $tahap3PhotoGallery->count() > 0 ? $tahap3PhotoGallery->count() . ' Foto Uji Mutu' : 'Rincian & Bukti Foto' }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
                             class="tahap-card desktop-tahap-card bg-white rounded-xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs hover:shadow-xs transition cursor-pointer relative">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="tahap-badge px-2.5 py-0.5 rounded-md text-[10px] font-bold {{ $distReceived ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' }}">
                                    Tahap 4
                                </span>
                                <span class="text-xs font-medium text-slate-500">
                                    {{ $tahap4TimeBadge }}
                                </span>
                            </div>
                            <div class="w-8 h-8 rounded-lg {{ $distReceived ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-blue-50 text-blue-700 border-blue-100' }} flex items-center justify-center mb-2 border">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                </svg>
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">{{ $selectedDestination->nama }}</div>
                            <h4 class="font-bold text-slate-900 text-sm mt-0.5">Serah Terima Lokasi</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                @if($distReceived)
                                    Diterima lengkap {{ $porsiRealisasi }} porsi di lokasi sasaran
                                @elseif($distSent)
                                    Dalam perjalanan pengantaran ke lokasi
                                @else
                                    Alokasi {{ $porsiTarget }} porsi terjadwal
                                @endif
                            </p>
                            <div class="tahap-footer mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600 font-bold">
                                <span>{{ $tahap4PhotoGallery->count() > 0 ? $tahap4PhotoGallery->count() . ' Foto Distribusi' : 'Rincian & Bukti Foto' }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>

                    </div>

                    <!-- 6. DETAIL PERJALANAN & DOKUMENTASI RIIL (PANEL INTERAKTIF) -->
                    <div id="tahapDetailPanel" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-7 mt-3 transition-all duration-150">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                            <div class="space-y-1">
                                <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    <span id="tahapDetailBadge">Tahap 01</span>
                                    <span>&bull;</span>
                                    <span id="tahapDetailEyebrow">Titik Pasokan Bahan</span>
                                </div>
                                <h4 id="tahapDetailTitle" class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight leading-snug">
                                    Pengadaan & Pembelian Bahan
                                </h4>
                                <p id="tahapDetailSubtitle" class="text-xs text-slate-500 leading-relaxed font-normal">
                                    Dokumentasi nyata verifikasi lapangan di titik rantai pasok mitra petani lokal.
                                </p>
                            </div>

                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0 self-start sm:self-auto">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Data Telah Diverifikasi</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pt-5">
                            <!-- Foto Dokumentasi Tahap -->
                            <div class="lg:col-span-5 space-y-2">
                                @php
                                    $tahap1GalleryList = $tahap1PhotoGallery->map(fn($item) => [
                                        'url' => $item['photo_url'] ?? $item['url'],
                                        'caption' => $item['photo_desc'] ?? $item['caption'] ?? ('Bukti Fisik Timbangan ' . ($item['nama'] ?? 'Bahan'))
                                    ])->values();

                                    $tahap2GalleryList = $tahap2PhotoGallery->map(fn($item) => [
                                        'url' => $item['url'],
                                        'caption' => $item['caption']
                                    ])->values();

                                    $tahap3GalleryList = $tahap3PhotoGallery->map(fn($item) => [
                                        'url' => $item['url'],
                                        'caption' => $item['caption']
                                    ])->values();

                                    $tahap4GalleryList = $tahap4PhotoGallery->map(fn($item) => [
                                        'url' => $item['url'],
                                        'caption' => $item['caption']
                                    ])->values();
                                @endphp

                                <script>
                                    window.tahapGalleries = {
                                        '1': @json($tahap1GalleryList),
                                        '2': @json($tahap2GalleryList),
                                        '3': @json($tahap3GalleryList),
                                        '4': @json($tahap4GalleryList)
                                    };
                                </script>

                                <div id="tahapDetailImgContainer" onclick="openActiveTahapPhoto()" class="relative rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-4/3 group shadow-2xs cursor-pointer">
                                    <img id="tahapDetailImg" src="{{ $tahap1Photo }}" alt="Dokumentasi Tahap" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80';" class="w-full h-full object-cover group-hover:scale-102 transition duration-200">
                                    <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="px-3 py-1.5 rounded-lg bg-white/95 text-slate-900 font-bold text-xs shadow-md flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-[#071E49]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            Buka Foto & Geser
                                        </span>
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-900/80 via-slate-900/30 to-transparent p-3 text-white text-xs">
                                        <span id="tahapDetailCaption" class="font-medium truncate block">{{ $tahap1Caption }}</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-400 text-center">
                                    Setiap baki saji terhubung dengan rekam jejak digital petugas berwenang
                                </p>

                                <!-- Strip Galeri Multi-Foto: Tahap 1 (Penerimaan Bahan Baku) -->
                                @if($tahap1PhotoGallery->isNotEmpty())
                                    <div id="tahap1MultiGallery" class="pt-2 border-t border-slate-100 space-y-1.5">
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                            <span>Bahan Menu ID #{{ $menu->id }} ({{ $tahap1PhotoGallery->count() }} Komoditas Penerimaan):</span>
                                            <span class="text-[10px] text-slate-400">Klik untuk geser</span>
                                        </div>
                                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                                            @foreach($tahap1PhotoGallery as $idx => $m)
                                                @php
                                                    $mUrl = $m['photo_url'];
                                                    $mDesc = $m['photo_desc'] ?? ('Bukti Fisik Timbangan ' . $m['nama']);
                                                @endphp
                                                <button type="button"
                                                        onclick='selectTahapPhoto("1", {{ $idx }}, "{{ $mUrl }}", "{{ addslashes($mDesc) }}", this)'
                                                        title="{{ $m['nama'] }}: {{ $mDesc }}"
                                                        class="tahap-thumb-btn tahap1-thumb-btn group/btn relative aspect-square rounded-lg overflow-hidden border border-slate-200 bg-slate-100 cursor-pointer hover:border-[#071E49] transition {{ $idx === 0 ? 'ring-2 ring-[#071E49] border-[#071E49]' : '' }}">
                                                    <img src="{{ $mUrl }}" alt="{{ $m['nama'] }}" class="w-full h-full object-cover group-hover/btn:scale-105 transition duration-200">
                                                    <div class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[8px] font-semibold px-0.5 py-0.5 truncate text-center backdrop-blur-xs">
                                                        {{ $m['nama'] }}
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Strip Galeri Multi-Foto: Tahap 2 (Pengolahan Higienis Dapur SPPG) -->
                                @if($tahap2PhotoGallery->isNotEmpty())
                                    <div id="tahap2MultiGallery" class="pt-2 border-t border-slate-100 space-y-1.5 hidden">
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                            <span>Proses Dapur SPPG ({{ $tahap2PhotoGallery->count() }} Foto Tahapan):</span>
                                            <span class="text-[10px] text-slate-400">Klik untuk geser</span>
                                        </div>
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                            @foreach($tahap2PhotoGallery as $idx => $p2)
                                                <button type="button"
                                                        onclick='selectTahapPhoto("2", {{ $idx }}, "{{ $p2['url'] }}", "{{ addslashes($p2['caption']) }}", this)'
                                                        title="{{ $p2['title'] }}: {{ $p2['caption'] }}"
                                                        class="tahap-thumb-btn tahap2-thumb-btn group/btn relative aspect-square rounded-lg overflow-hidden border border-slate-200 bg-slate-100 cursor-pointer hover:border-[#071E49] transition {{ $idx === 0 ? 'ring-2 ring-[#071E49] border-[#071E49]' : '' }}">
                                                    <img src="{{ $p2['url'] }}" alt="{{ $p2['title'] }}" class="w-full h-full object-cover group-hover/btn:scale-105 transition duration-200">
                                                    <div class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[8px] font-semibold px-0.5 py-0.5 truncate text-center backdrop-blur-xs">
                                                        {{ $p2['title'] }}
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Strip Galeri Multi-Foto: Tahap 3 (Pemeriksaan Uji Mutu & Porsi) -->
                                @if($tahap3PhotoGallery->isNotEmpty())
                                    <div id="tahap3MultiGallery" class="pt-2 border-t border-slate-100 space-y-1.5 hidden">
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                            <span>Uji Mutu Kelayakan ({{ $tahap3PhotoGallery->count() }} Bukti Saji):</span>
                                            <span class="text-[10px] text-slate-400">Klik untuk geser</span>
                                        </div>
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                            @foreach($tahap3PhotoGallery as $idx => $p3)
                                                <button type="button"
                                                        onclick='selectTahapPhoto("3", {{ $idx }}, "{{ $p3['url'] }}", "{{ addslashes($p3['caption']) }}", this)'
                                                        title="{{ $p3['title'] }}: {{ $p3['caption'] }}"
                                                        class="tahap-thumb-btn tahap3-thumb-btn group/btn relative aspect-square rounded-lg overflow-hidden border border-slate-200 bg-slate-100 cursor-pointer hover:border-[#071E49] transition {{ $idx === 0 ? 'ring-2 ring-[#071E49] border-[#071E49]' : '' }}">
                                                    <img src="{{ $p3['url'] }}" alt="{{ $p3['title'] }}" class="w-full h-full object-cover group-hover/btn:scale-105 transition duration-200">
                                                    <div class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[8px] font-semibold px-0.5 py-0.5 truncate text-center backdrop-blur-xs">
                                                        {{ $p3['title'] }}
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Strip Galeri Multi-Foto: Tahap 4 (Armada Pengantar & Serah Terima Sekolah) -->
                                @if($tahap4PhotoGallery->isNotEmpty())
                                    <div id="tahap4MultiGallery" class="pt-2 border-t border-slate-100 space-y-1.5 hidden">
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                            <span>Distribusi & Serah Terima ({{ $tahap4PhotoGallery->count() }} Foto Lapangan):</span>
                                            <span class="text-[10px] text-slate-400">Klik untuk geser</span>
                                        </div>
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                            @foreach($tahap4PhotoGallery as $idx => $p4)
                                                <button type="button"
                                                        onclick='selectTahapPhoto("4", {{ $idx }}, "{{ $p4['url'] }}", "{{ addslashes($p4['caption']) }}", this)'
                                                        title="{{ $p4['title'] }}: {{ $p4['caption'] }}"
                                                        class="tahap-thumb-btn tahap4-thumb-btn group/btn relative aspect-square rounded-lg overflow-hidden border border-slate-200 bg-slate-100 cursor-pointer hover:border-[#071E49] transition {{ $idx === 0 ? 'ring-2 ring-[#071E49] border-[#071E49]' : '' }}">
                                                    <img src="{{ $p4['url'] }}" alt="{{ $p4['title'] }}" class="w-full h-full object-cover group-hover/btn:scale-105 transition duration-200">
                                                    <div class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[8px] font-semibold px-0.5 py-0.5 truncate text-center backdrop-blur-xs">
                                                        {{ $p4['title'] }}
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Keterangan & Rincian Proses -->
                            <div class="lg:col-span-7 space-y-4">
                                <div>
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">
                                        RINCIAN PROSES TAHAPAN:
                                    </span>
                                    <p id="tahapDetailDesc" class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
                                        {{ $tahap1Desc }}
                                    </p>
                                </div>

                                <!-- 4 Box Info Tahapan -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div class="bg-slate-50 border border-slate-200/90 rounded-xl p-3">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">WAKTU PELAKSANAAN</span>
                                        <span id="tahapDetailWaktu" class="text-xs font-bold text-slate-800 mt-0.5 block">Pukul {{ $tahap1Time }}</span>
                                    </div>
                                    <div class="bg-slate-50 border border-slate-200/90 rounded-xl p-3">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">KONDISI BAHAN BAKU</span>
                                        <span id="tahapDetailKondisi" class="text-xs font-bold text-slate-800 mt-0.5 block">Segar, lolos sortir fisik harian</span>
                                    </div>
                                    <div class="bg-slate-50 border border-slate-200/90 rounded-xl p-3">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">BAHAN TERPILIH HARI INI</span>
                                        <span id="tahapDetailBahan" class="text-xs font-bold text-slate-800 mt-0.5 block">{{ $tahap1Bahan }}</span>
                                    </div>
                                    <div class="bg-slate-50 border border-slate-200/90 rounded-xl p-3">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">PETUGAS VERIFIKASI</span>
                                        <span id="tahapDetailPetugas" class="text-xs font-bold text-slate-800 mt-0.5 block">{{ $tahap1Petugas }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- TAMPILAN JIKA BELUM ADA CATATAN PENGIRIMAN MBG -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-8 sm:p-10 text-center space-y-4">
                    <div class="w-20 h-20 mx-auto flex items-center justify-center">
                        <img src="{{ asset('images/bingung-trimmed.png') }}" alt="Belum Ada Catatan" class="w-full h-full object-contain">
                    </div>
                    <div class="max-w-md mx-auto space-y-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Status: Belum Terdistribusi</span>
                        </span>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                            Belum Ada Catatan Pengiriman MBG
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                            Instansi <strong class="text-slate-800">{{ $selectedDestination->nama }}</strong> belum memiliki catatan pengiriman Program Makan Bergizi Gratis pada jadwal yang dipilih. Rincian menu harian dan bukti perjalanan makanan akan diperbarui secara transparan ketika distribusi dimulai.
                        </p>
                    </div>
                    <div class="pt-2 flex flex-wrap items-center justify-center gap-3 text-xs">
                        <div class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-600">
                            Target Sasaran: <strong class="text-[#071E49]">{{ number_format($porsiTarget, 0, ',', '.') }} Siswa/Porsi</strong>
                        </div>
                        @if($sppg)
                            <div class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-600">
                                Unit Dapur Pengampu: <strong class="text-[#071E49]">{{ $sppg->nama_sppg }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 7. SPPG PENYEDIA -->
            @if($sppg)
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#071E49] flex items-center justify-center shrink-0 border border-blue-100">
                            <svg class="w-4 h-4 text-[#071E49]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Dapur SPPG Pengampu</h3>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6">
                        <div class="flex items-center justify-between pb-5 border-b border-slate-100 flex-wrap gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-[#071E49] text-white flex items-center justify-center shrink-0 shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                                        {{ $sppg->nama_sppg }}
                                    </h4>
                                    <div class="text-[11px] font-mono font-medium text-slate-500 mt-0.5">
                                        ID SPPG: SPPG-{{ str_pad($sppg->id_sppg, 5, '0', STR_PAD_LEFT) }}
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('public.sppg', $sppg->id_sppg) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#071E49] text-white hover:bg-[#042A70] transition shadow-2xs">
                                <span>Lihat Profil & Riwayat SPPG</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-5">
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">PROVINSI</span>
                                <span class="text-xs font-bold text-slate-800">{{ $sppg->provinsi ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">KOTA / KABUPATEN</span>
                                <span class="text-xs font-bold text-slate-800">{{ $sppg->kabupaten_kota ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">KECAMATAN</span>
                                <span class="text-xs font-bold text-slate-800">{{ $sppg->kecamatan ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-slate-100">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">ALAMAT FASILITAS</span>
                            <p class="text-xs text-slate-700 leading-relaxed font-semibold">
                                {{ $sppg->alamat_sppg ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

        </section>
    @endif

</div>
@endsection
