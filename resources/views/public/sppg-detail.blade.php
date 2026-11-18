@extends('layouts.public')

@section('title', ($sppg->nama_sppg ?? 'Dapur SPPG Sehat Bersama') . ' - Profil & Transparansi Unit SPPG')

@push('styles')
<style>
    /* Anti-slop micro-interactions & responsive layout styles */
    .tahap-card {
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease, background-color 0.2s ease;
        -webkit-tap-highlight-color: transparent;
    }
    .tahap-card:hover {
        transform: translateY(-2px);
    }
    .tahap-card:active {
        transform: scale(0.985);
    }
    .tahap-card.is-active {
        transform: translateY(-2px);
    }
    .tahap-drawer {
        transition: opacity 0.25s ease, transform 0.25s ease;
    }
    /* Touch target minimum standards (taste-skill 44px rule) */
    .touch-target-44 {
        min-height: 44px;
        min-width: 44px;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-5 sm:space-y-7">

    <!-- BREADCRUMB BAR (Clean & Mobile Adaptive) -->
    <nav class="flex items-center justify-between sm:justify-start gap-2 text-xs font-semibold text-slate-500 py-1" aria-label="Breadcrumb">
        <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
            <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1 text-slate-500 hover:text-blue-700 transition shrink-0 py-1 touch-target-44 sm:min-h-0 sm:py-0">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Beranda</span>
            </a>
            <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-slate-600 truncate max-w-[120px] sm:max-w-none">{{ $sppg->kabupaten_kota ?? 'Kota Malang' }}</span>
            <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="font-bold text-slate-900 truncate max-w-[150px] sm:max-w-none">{{ $sppg->nama_sppg ?? 'Dapur SPPG' }}</span>
        </div>
        <div class="shrink-0 ml-auto sm:ml-2">
            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200/90 shadow-2xs">
                {{ $unitCode ?? 'SPPG-2026-005' }}
            </span>
        </div>
    </nav>

    <!-- HERO PROFIL DAPUR SPPG (Dynamic Split Grid for HP, Tablet & Desktop) -->
    <section class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-4 sm:p-6 lg:p-7 shadow-xs relative overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 lg:gap-7 items-center">
            
            {{-- Kolom Foto Gedung Dapur --}}
            <div class="md:col-span-5 lg:col-span-5">
                <div class="relative rounded-2xl overflow-hidden aspect-[16/10] sm:aspect-[4/3] md:aspect-[4/3] lg:h-[280px] w-full shadow-2xs bg-slate-100 group">
                    @php
                        $fotoKantor = asset('images/dapur-sppg-sample.jpg');
                        if (!empty($sppg->foto_kantor_sppg)) {
                            $fotoKantor = str_starts_with($sppg->foto_kantor_sppg, 'http') ? $sppg->foto_kantor_sppg : asset('storage/'.$sppg->foto_kantor_sppg);
                        }
                    @endphp
                    <img 
                        src="{{ $fotoKantor }}" 
                        alt="{{ $sppg->nama_sppg ?? 'Dapur SPPG' }}" 
                        class="w-full h-full object-cover object-center group-hover:scale-102 transition duration-500"
                        onerror="this.onerror=null; this.src='{{ asset('images/dapur-sppg-sample.jpg') }}';"
                    >
                    
                    <!-- Badge Atas Kiri: Sentra Produksi Pangan Gizi -->
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-md text-slate-900 border border-slate-200/60 text-[11px] font-bold px-3 py-1.5 rounded-xl shadow-xs inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Sentra Produksi Pangan Gizi</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Info Dapur (Kanan) -->
            <div class="md:col-span-7 lg:col-span-7 space-y-4">
                
                <!-- Nama Dapur & Status Badge -->
                <div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 tracking-tight leading-snug">
                            {{ $sppg->nama_sppg ?? 'Dapur SPPG Sehat Bersama' }}
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($sppg->status ?? 'aktif') === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }} inline-flex items-center gap-1.5 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full {{ ($sppg->status ?? 'aktif') === 'aktif' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ ($sppg->status ?? 'aktif') === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mt-1.5">
                        Sentra pemenuhan gizi anak sekolah berstandar higienis nasional wilayah {{ $sppg->kecamatan ? 'Kec. ' . $sppg->kecamatan : '' }} {{ $sppg->kabupaten_kota ?? 'Kota Malang' }}.
                    </p>
                </div>

                <!-- Alamat Operasional & Radius -->
                <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-3 sm:p-3.5 space-y-2">
                    <div class="flex items-start gap-2.5 text-xs">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div class="min-w-0">
                            <span class="font-bold text-slate-900">Alamat Operasional:</span>
                            <span class="text-slate-600 block mt-0.5 leading-relaxed">
                                {{ $sppg->alamat_sppg ?? 'Jalan Teluk Cendrawasih' }}{{ $sppg->kecamatan ? ', Kec. ' . $sppg->kecamatan : '' }}{{ $sppg->kabupaten_kota ? ', ' . $sppg->kabupaten_kota : '' }}{{ $sppg->provinsi ? ', ' . $sppg->provinsi : '' }}
                            </span>
                        </div>
                    </div>
                    <div class="sm:pl-6.5">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200/60 text-[11px] font-bold">
                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Radius distribusi aman terkendali: hingga 5 km</span>
                        </div>
                    </div>
                </div>

                <!-- Jam Masak Subuh & Jadwal Kirim Santap -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                    <!-- Jam Masak Subuh -->
                    <div class="bg-amber-50/40 border border-amber-200/70 rounded-2xl p-3 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-bold text-amber-900">Jam Masak Subuh</div>
                            <div class="text-sm font-black text-slate-900 mt-0.5">04.30 - 09.30 WIB</div>
                        </div>
                        <div class="text-amber-600 p-2 rounded-xl bg-amber-100/70 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Jadwal Kirim Santap -->
                    <div class="bg-emerald-50/40 border border-emerald-200/70 rounded-2xl p-3 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-bold text-emerald-900">Jadwal Kirim Santap</div>
                            <div class="text-sm font-black text-slate-900 mt-0.5">09.30 - 11.00 WIB</div>
                        </div>
                        <div class="text-emerald-600 p-2 rounded-xl bg-emerald-100/70 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- MIDDLE STATS ROW (Dynamic Grid: HP Full Width, iPad & Desktop Balanced Bento) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 lg:gap-6 items-stretch">
        <!-- Kolom Kiri: Produktivitas - Kapasitas Dapur -->
        <div class="md:col-span-5 lg:col-span-4 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                        PRODUKTIVITAS
                    </span>
                    <div class="w-7 h-7 rounded-full bg-[#071E49] text-white flex items-center justify-center shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs sm:text-sm font-bold text-slate-800 mt-3">Target Porsi Harian</div>
                <div class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black text-[#071E49] tracking-tight">
                        {{ number_format($kapasitasHarian, 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500">porsi/hari</span>
                </div>
            </div>

            <div class="mt-4 pt-1">
                <!-- Progress Bar -->
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-[#071E49] h-full rounded-full transition-all duration-700" style="width: {{ $persentaseDistribusi }}%"></div>
                </div>
                <div class="flex items-center justify-between text-xs mt-2.5">
                    <span class="font-bold text-slate-700 text-[11px]">Terdistribusi: {{ number_format($porsiTerdistribusi, 0, ',', '.') }} porsi</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $persentaseDistribusi }}% Tuntas
                    </span>
                </div>
            </div>
        </div>

        <!-- Kolom Tengah: Maskot makan.png (Responsif & Proporsional di HP & Tablet) -->
        <div class="md:col-span-2 lg:col-span-4 flex items-center justify-center py-1 sm:py-2 md:py-0">
            <img 
                src="{{ asset('images/makan.png') }}" 
                alt="Maskot Santap Bergizi" 
                class="w-32 sm:w-40 md:w-48 lg:w-56 aspect-square object-contain hover:scale-105 active:scale-95 transition-transform duration-300 ease-out cursor-pointer select-none"
                onerror="this.onerror=null; this.src='{{ asset('images/makan-trimmed.png') }}';"
            >
        </div>

        <!-- Kolom Kanan: Transparansi - Tingkat Pencatatan -->
        <div class="md:col-span-5 lg:col-span-4 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-100">
                        TRANSPARANSI
                    </span>
                    <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs sm:text-sm font-bold text-slate-800 mt-3">Tingkat Pencatatan</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-black text-[#071E49] tracking-tight">100%</span>
                    <span class="text-[11px] font-bold text-emerald-700">Tercatat Digital</span>
                </div>
            </div>

            <div class="mt-3 pt-1">
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Aliran porsi dari penyiapan bahan, suhu masak, uji gizi, hingga serah terima sekolah tercatat dan diaudit digital.
                </p>
                <div class="mt-2.5 flex items-center gap-1.5 text-[10px] font-bold text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Audit Trail Terverifikasi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- AKTIVITAS SIKLUS TERAKHIR : MASING-MASING PROSES -->
    <section id="aktivitas-siklus" class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-4 sm:p-6 lg:p-7 shadow-xs space-y-4 sm:space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                        Aktivitas Siklus Terakhir : Masing-masing Proses
                    </h2>
                </div>
                <p class="text-xs text-slate-500 mt-1 sm:pl-8">
                    Ketuk salah satu kartu tahapan di bawah ini untuk membuka detail proses operasional lengkap ke bawah
                </p>
            </div>
            <div class="shrink-0 self-start sm:self-center flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Siklus Terkini Tuntas 100%</span>
                </span>
                <span class="hidden md:inline-flex px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    {{ $activeBatch->batch_code ?? 'BTH-20261117-001' }}
                </span>
            </div>
        </div>

        <!-- 4 Cards Grid Tahapan (2x2 di HP agar langsung tampak di layar, 4 di Tablet & Desktop) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
            
            <!-- Tahap 01: Bahan Segar Datang -->
            <div 
                id="card-tahap-1" 
                onclick="toggleTahapDetail(1)"
                role="button"
                tabindex="0"
                class="tahap-card bg-blue-50/25 hover:bg-blue-50/60 border border-blue-100 hover:border-blue-300 rounded-2xl p-3 sm:p-4 flex flex-col justify-between cursor-pointer transition shadow-2xs group relative"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-blue-700">Tahap 01</span>
                        <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span class="hidden xs:inline">Beres</span>
                        </span>
                    </div>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-blue-500 group-hover:bg-blue-600 text-white flex items-center justify-center mt-2.5 sm:mt-3 shadow-2xs transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="font-black text-slate-900 text-xs sm:text-sm mt-2.5 sm:mt-3 group-hover:text-blue-900 transition-colors leading-snug">
                        Bahan Segar Datang
                    </h3>
                    <p class="hidden sm:block text-xs text-slate-500 leading-relaxed mt-1 line-clamp-2">
                        Penerimaan & sortir komoditas pangan segar subuh dari mitra petani lokal & Bulog.
                    </p>
                </div>
                
                <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-blue-100/70 flex items-center justify-between">
                    <div class="inline-flex items-center gap-1 bg-white border border-slate-200/80 text-blue-900 text-[10px] sm:text-[11px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md shadow-2xs">
                        <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $stepPersiapan?->started_at ? $stepPersiapan->started_at->format('H.i') . ' WIB' : '05.30 WIB' }}</span>
                    </div>
                    <div class="flex items-center gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] font-bold text-blue-600 group-hover:translate-y-0.5 transition-transform">
                        <span id="btn-text-1">Rincian</span>
                        <svg id="chevron-1" class="w-3.5 h-3.5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tahap 02: Masak Matang -->
            <div 
                id="card-tahap-2" 
                onclick="toggleTahapDetail(2)"
                role="button"
                tabindex="0"
                class="tahap-card bg-amber-50/25 hover:bg-amber-50/60 border border-amber-100 hover:border-amber-300 rounded-2xl p-3 sm:p-4 flex flex-col justify-between cursor-pointer transition shadow-2xs group relative"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-amber-700">Tahap 02</span>
                        <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span class="hidden xs:inline">Beres</span>
                        </span>
                    </div>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-500 group-hover:bg-amber-600 text-white flex items-center justify-center mt-2.5 sm:mt-3 shadow-2xs transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                        </svg>
                    </div>
                    <h3 class="font-black text-slate-900 text-xs sm:text-sm mt-2.5 sm:mt-3 group-hover:text-amber-900 transition-colors leading-snug">
                        Masak Matang
                    </h3>
                    <p class="hidden sm:block text-xs text-slate-500 leading-relaxed mt-1 line-clamp-2">
                        Pengolahan makanan bergizi seimbang dengan suhu higienis & menu teruji.
                    </p>
                </div>

                <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-amber-100/70 flex items-center justify-between">
                    <div class="inline-flex items-center gap-1 bg-white border border-slate-200/80 text-amber-900 text-[10px] sm:text-[11px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md shadow-2xs">
                        <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>06.15 - 08.00 WIB</span>
                    </div>
                    <div class="flex items-center gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] font-bold text-amber-700 group-hover:translate-y-0.5 transition-transform">
                        <span id="btn-text-2">Rincian</span>
                        <svg id="chevron-2" class="w-3.5 h-3.5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tahap 03: Cek Gizi & Pack -->
            <div 
                id="card-tahap-3" 
                onclick="toggleTahapDetail(3)"
                role="button"
                tabindex="0"
                class="tahap-card bg-emerald-50/25 hover:bg-emerald-50/60 border border-emerald-100 hover:border-emerald-300 rounded-2xl p-3 sm:p-4 flex flex-col justify-between cursor-pointer transition shadow-2xs group relative"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-emerald-700">Tahap 03</span>
                        <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span class="hidden xs:inline">Beres</span>
                        </span>
                    </div>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-500 group-hover:bg-emerald-600 text-white flex items-center justify-center mt-2.5 sm:mt-3 shadow-2xs transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="font-black text-slate-900 text-xs sm:text-sm mt-2.5 sm:mt-3 group-hover:text-emerald-900 transition-colors leading-snug">
                        Cek Gizi & Pack
                    </h3>
                    <p class="hidden sm:block text-xs text-slate-500 leading-relaxed mt-1 line-clamp-2">
                        Uji fisik organoleptik rasa, timbang porsi presisi, dan uji nutrisi ahli gizi.
                    </p>
                </div>

                <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-emerald-100/70 flex items-center justify-between">
                    <div class="inline-flex items-center gap-1 bg-white border border-slate-200/80 text-emerald-900 text-[10px] sm:text-[11px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md shadow-2xs">
                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $qcCheck?->checked_at ? $qcCheck->checked_at->format('H.i') . ' WIB' : '08.15 - 09.00 WIB' }}</span>
                    </div>
                    <div class="flex items-center gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] font-bold text-emerald-700 group-hover:translate-y-0.5 transition-transform">
                        <span id="btn-text-3">Rincian</span>
                        <svg id="chevron-3" class="w-3.5 h-3.5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tahap 04: Meluncur ke Sekolah -->
            <div 
                id="card-tahap-4" 
                onclick="toggleTahapDetail(4)"
                role="button"
                tabindex="0"
                class="tahap-card bg-indigo-50/25 hover:bg-indigo-50/60 border border-indigo-100 hover:border-indigo-300 rounded-2xl p-3 sm:p-4 flex flex-col justify-between cursor-pointer transition shadow-2xs group relative"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-indigo-700">Tahap 04</span>
                        <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span class="hidden xs:inline">Beres</span>
                        </span>
                    </div>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-600 group-hover:bg-indigo-700 text-white flex items-center justify-center mt-2.5 sm:mt-3 shadow-2xs transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="font-black text-slate-900 text-xs sm:text-sm mt-2.5 sm:mt-3 group-hover:text-indigo-900 transition-colors leading-snug">
                        Meluncur ke Sekolah
                    </h3>
                    <p class="hidden sm:block text-xs text-slate-500 leading-relaxed mt-1 line-clamp-2">
                        Distribusi logistik rantai hangat ke sekolah binaan dengan armada terverifikasi.
                    </p>
                </div>

                <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-indigo-100/70 flex items-center justify-between">
                    <div class="inline-flex items-center gap-1 bg-white border border-slate-200/80 text-indigo-900 text-[10px] sm:text-[11px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md shadow-2xs">
                        <svg class="w-3 h-3 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>09.15 WIB</span>
                    </div>
                    <div class="flex items-center gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] font-bold text-indigo-600 group-hover:translate-y-0.5 transition-transform">
                        <span id="btn-text-4">Rincian</span>
                        <svg id="chevron-4" class="w-3.5 h-3.5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        <!-- DETAIL CONTAINER AKORDION (Membuka ke Bawah Secara Dinamis & Responsif) -->
        <div 
            id="tahap-drawer-container" 
            class="tahap-drawer hidden overflow-hidden rounded-2xl border border-slate-200/90 bg-slate-50/70 p-3.5 sm:p-5 lg:p-6 shadow-xs mt-3 sm:mt-4"
        >
            <!-- Drawer Header & Navigasi Cepat Antar Tahap -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 sm:pb-4 border-b border-slate-200/80">
                <div class="flex items-center gap-2.5">
                    <span id="drawer-badge-tahap" class="px-2.5 py-1 rounded-md text-xs font-black bg-blue-600 text-white shadow-2xs">
                        Tahap 01
                    </span>
                    <div>
                        <h4 id="drawer-title" class="text-sm sm:text-base font-black text-slate-900 leading-tight">
                            Rincian Bahan Segar Datang
                        </h4>
                        <div id="drawer-subtitle" class="text-xs text-slate-500 mt-0.5">
                            Penerimaan & Sortir Bahan Baku Subuh dari Mitra Lokal
                        </div>
                    </div>
                </div>

                <!-- Tab Navigasi Cepat & Tombol Tutup -->
                <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                    <div class="inline-flex p-1 bg-white border border-slate-200 rounded-xl shadow-2xs text-xs font-bold text-slate-600">
                        <button type="button" onclick="toggleTahapDetail(1)" id="tab-btn-1" class="px-2.5 py-1 rounded-lg transition min-h-[36px] sm:min-h-0">T-01</button>
                        <button type="button" onclick="toggleTahapDetail(2)" id="tab-btn-2" class="px-2.5 py-1 rounded-lg transition min-h-[36px] sm:min-h-0">T-02</button>
                        <button type="button" onclick="toggleTahapDetail(3)" id="tab-btn-3" class="px-2.5 py-1 rounded-lg transition min-h-[36px] sm:min-h-0">T-03</button>
                        <button type="button" onclick="toggleTahapDetail(4)" id="tab-btn-4" class="px-2.5 py-1 rounded-lg transition min-h-[36px] sm:min-h-0">T-04</button>
                    </div>
                    <button 
                        type="button" 
                        onclick="closeTahapDrawer()"
                        class="p-2 sm:p-1.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition shadow-2xs min-h-[40px] min-w-[40px] sm:min-h-0 sm:min-w-0 flex items-center justify-center"
                        title="Tutup Rincian"
                        aria-label="Tutup Rincian"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- KONTEN RINCIAN TAHAP 01 : BAHAN SEGAR DATANG -->
            <div id="content-tahap-1" class="tahap-content hidden pt-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
                    <!-- Foto Dokumentasi di Kiri -->
                    <div class="md:col-span-5 lg:col-span-4">
                        <div onclick="openPhotoModal(1, 0)" class="relative rounded-2xl overflow-hidden aspect-[16/10] sm:aspect-[4/3] md:aspect-[3/4] w-full bg-slate-100 shadow-2xs border border-slate-200/80 group cursor-pointer" title="Klik untuk membuka pop-up galeri dan geser foto">
                            <img 
                                src="https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=80" 
                                alt="Penerimaan Bahan Baku Segar" 
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500"
                            >
                            <!-- Badge Preview Banyak Foto -->
                            <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-[10px] font-extrabold flex items-center gap-1 shadow-sm border border-white/20">
                                <svg class="w-3 h-3 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>4 Foto</span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent flex flex-col justify-end p-3.5 group-hover:from-slate-950/90 transition-colors">
                                <span class="self-start px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white shadow-2xs">Dokumentasi Bahan Masuk</span>
                                <p class="text-xs font-bold text-white mt-1 leading-snug">Sortir Sayur & Pangan Segar Petani</p>
                                <div class="mt-2 flex items-center gap-1 text-[11px] font-bold text-amber-300 group-hover:text-amber-200">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Buka Galeri &amp; Geser Foto &rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Di Samping Kanannya: Card Waktu, Penanggung Jawab, Standar Mutu & Tabel -->
                    <div class="md:col-span-7 lg:col-span-8 flex flex-col justify-between gap-3">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">WAKTU SORTIR SUBUH</div>
                                <div class="text-xs sm:text-sm font-extrabold text-blue-900 mt-0.5">
                                    {{ $stepPersiapan?->started_at ? $stepPersiapan->started_at->format('H.i') . ' WIB' : '05.30 WIB' }}
                                </div>
                            </div>
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">PENANGGUNG JAWAB</div>
                                <div class="text-xs sm:text-sm font-extrabold text-slate-900 mt-0.5 truncate">
                                    {{ $stepPersiapan?->recorder?->nama ?? $nutritionistName ?? 'Petugas Logistik SPPG' }}
                                </div>
                            </div>
                            <div class="col-span-2 sm:col-span-1 bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">STANDAR MUTU</div>
                                <div class="text-xs sm:text-sm font-extrabold text-emerald-700 mt-0.5 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span>Lolos Sortir 100% Grade A</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Komoditas Bahan Baku dari Database -->
                        <div class="bg-white border border-slate-200/80 rounded-xl overflow-hidden shadow-2xs flex-grow">
                            <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700">Daftar Bahan & Mitra Pemasok Terverifikasi</span>
                                <span class="text-[11px] font-semibold text-slate-500">{{ $materialList->count() }} Komoditas</span>
                            </div>
                            <div class="divide-y divide-slate-100 max-h-48 overflow-y-auto">
                                @foreach($materialList as $mat)
                                    <div class="px-3.5 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-2 text-xs">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                            <div class="truncate">
                                                <span class="font-bold text-slate-900">{{ $mat['nama'] }}</span>
                                                <span class="text-slate-400 text-[11px] ml-1">({{ $mat['kategori'] }})</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                                {{ $mat['supplier'] }}
                                            </span>
                                            <span class="text-emerald-700 font-black text-[11px] bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                                                Lolos Sortir
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($stepPersiapan?->note)
                            <div class="p-3 bg-blue-50/60 border border-blue-100 rounded-xl text-xs text-blue-900 leading-relaxed">
                                <span class="font-bold">Catatan Verifikasi Masuk:</span> {!! nl2br(e($stepPersiapan->note)) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KONTEN RINCIAN TAHAP 02 : MASAK MATANG -->
            <div id="content-tahap-2" class="tahap-content hidden pt-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
                    <!-- Foto Dokumentasi di Kiri -->
                    <div class="md:col-span-5 lg:col-span-4">
                        <div onclick="openPhotoModal(2, 0)" class="relative rounded-2xl overflow-hidden aspect-[16/10] sm:aspect-[4/3] md:aspect-[3/4] w-full bg-slate-100 shadow-2xs border border-slate-200/80 group cursor-pointer" title="Klik untuk membuka pop-up galeri dan geser foto">
                            <img 
                                src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80" 
                                alt="Proses Pengolahan Dapur MBG" 
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500"
                            >
                            <!-- Badge Preview Banyak Foto -->
                            <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-[10px] font-extrabold flex items-center gap-1 shadow-sm border border-white/20">
                                <svg class="w-3 h-3 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>4 Foto</span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent flex flex-col justify-end p-3.5 group-hover:from-slate-950/90 transition-colors">
                                <span class="self-start px-2 py-0.5 rounded text-[10px] font-bold bg-amber-600 text-white shadow-2xs">Dokumentasi Pengolahan</span>
                                <p class="text-xs font-bold text-white mt-1 leading-snug">Pemasakan Higienis Suhu &gt;85°C</p>
                                <div class="mt-2 flex items-center gap-1 text-[11px] font-bold text-amber-300 group-hover:text-amber-200">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Buka Galeri &amp; Geser Foto &rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Di Samping Kanannya: Card Waktu, Suhu, Metode, Menu & Catatan -->
                    <div class="md:col-span-7 lg:col-span-8 flex flex-col justify-between gap-3">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">JADWAL MEMASAK</div>
                                <div class="text-xs sm:text-sm font-extrabold text-amber-900 mt-0.5">
                                    {{ ($stepMasak?->started_at && $stepMasak?->ended_at) ? $stepMasak->started_at->format('H.i') . ' - ' . $stepMasak->ended_at->format('H.i') . ' WIB' : '06.15 - 08.00 WIB' }}
                                </div>
                            </div>
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SUHU PENGOLAHAN</div>
                                <div class="text-xs sm:text-sm font-extrabold text-emerald-700 mt-0.5">
                                    &gt; 85°C (Steril Higienis)
                                </div>
                            </div>
                            <div class="col-span-2 sm:col-span-1 bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">METODE MEMASAK</div>
                                <div class="text-xs sm:text-sm font-extrabold text-slate-900 mt-0.5">
                                    Kukus, Tumis & Rebus
                                </div>
                            </div>
                        </div>

                        <!-- Kartu Menu Aktif yang Dimasak -->
                        <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 sm:p-4 shadow-2xs space-y-1.5 flex-grow">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800 uppercase tracking-wide">
                                    MENU AKTIF SIKLUS INI
                                </span>
                                <span class="text-xs font-semibold text-slate-400">Batch: {{ $activeBatch->batch_code ?? 'BTH-20261117-001' }}</span>
                            </div>
                            <div class="text-sm sm:text-base font-extrabold text-slate-900">
                                {{ $menuNama }}
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                {{ $menuDeskripsi }}
                            </p>
                        </div>

                        @if($stepMasak?->note)
                            <div class="p-3 bg-amber-50/60 border border-amber-100 rounded-xl text-xs text-amber-900 leading-relaxed">
                                <span class="font-bold">Catatan Juru Masak:</span> {!! nl2br(e($stepMasak->note)) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KONTEN RINCIAN TAHAP 03 : CEK GIZI & PACK -->
            <div id="content-tahap-3" class="tahap-content hidden pt-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
                    <!-- Foto Dokumentasi di Kiri -->
                    <div class="md:col-span-5 lg:col-span-4">
                        <div onclick="openPhotoModal(3, 0)" class="relative rounded-2xl overflow-hidden aspect-[16/10] sm:aspect-[4/3] md:aspect-[3/4] w-full bg-slate-100 shadow-2xs border border-slate-200/80 group cursor-pointer" title="Klik untuk membuka pop-up galeri dan geser foto">
                            <img 
                                src="https://images.unsplash.com/photo-1576867757603-05b134ebc379?auto=format&fit=crop&w=800&q=80" 
                                alt="Pemeriksaan Kualitas Gizi & Kemasan" 
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500"
                            >
                            <!-- Badge Preview Banyak Foto -->
                            <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-[10px] font-extrabold flex items-center gap-1 shadow-sm border border-white/20">
                                <svg class="w-3 h-3 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>4 Foto</span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent flex flex-col justify-end p-3.5 group-hover:from-slate-950/90 transition-colors">
                                <span class="self-start px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white shadow-2xs">Dokumentasi Uji Gizi</span>
                                <p class="text-xs font-bold text-white mt-1 leading-snug">Uji Organoleptik & Segel Ompreng</p>
                                <div class="mt-2 flex items-center gap-1 text-[11px] font-bold text-amber-300 group-hover:text-amber-200">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Buka Galeri &amp; Geser Foto &rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Di Samping Kanannya: Card Ahli Gizi, Batas Aman, Porsi Lolos & Hasil Uji Fisik -->
                    <div class="md:col-span-7 lg:col-span-8 flex flex-col justify-between gap-3">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">AHLI GIZI QC</div>
                                <div class="text-xs sm:text-sm font-extrabold text-emerald-800 mt-0.5 truncate">
                                    {{ $nutritionistName }}
                                </div>
                            </div>
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">BATAS KONSUMSI</div>
                                <div class="text-xs sm:text-sm font-extrabold text-blue-900 mt-0.5">
                                    {{ $qcCheck?->consume_by ? \Carbon\Carbon::parse($qcCheck->consume_by)->format('H.i') . ' WIB' : '14.04 WIB' }}
                                </div>
                            </div>
                            <div class="col-span-2 sm:col-span-1 bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">PORSI LOLOS QC</div>
                                <div class="text-xs sm:text-sm font-extrabold text-slate-900 mt-0.5">
                                    {{ $qcCheck?->portions_ready ?? $porsiTerdistribusi }} Porsi Siap
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 sm:p-4 shadow-2xs space-y-2.5 flex-grow">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="text-xs font-bold text-slate-800">Hasil Pemeriksaan Uji Fisik Organoleptik:</span>
                            </div>
                            <div class="p-3 bg-emerald-50/50 border border-emerald-100 rounded-xl text-xs font-semibold text-emerald-900 leading-relaxed">
                                {{ $qcCheck?->reported_condition ?? 'Lolos Uji Fisik: Matang sempurna, aroma segar khas rempah alami, tekstur sayur renyah tidak layu, dan rasa teruji sesuai standar kecukupan gizi BGN.' }}
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-0.5">
                                <div class="flex items-center gap-2 text-slate-700 bg-slate-50 p-2 sm:p-2.5 rounded-xl border border-slate-100">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span class="font-bold">Uji Rasa & Aroma Lulus</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-700 bg-slate-50 p-2 sm:p-2.5 rounded-xl border border-slate-100">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span class="font-bold">Kemasan Ompreng Bersegel</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KONTEN RINCIAN TAHAP 04 : MELUNCUR KE SEKOLAH -->
            <div id="content-tahap-4" class="tahap-content hidden pt-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
                    <!-- Foto Dokumentasi di Kiri -->
                    <div class="md:col-span-5 lg:col-span-4">
                        <div onclick="openPhotoModal(4, 0)" class="relative rounded-2xl overflow-hidden aspect-[16/10] sm:aspect-[4/3] md:aspect-[3/4] w-full bg-slate-100 shadow-2xs border border-slate-200/80 group cursor-pointer" title="Klik untuk membuka pop-up galeri dan geser foto">
                            <img 
                                src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80" 
                                alt="Distribusi Terjadwal ke Sekolah" 
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500"
                            >
                            <!-- Badge Preview Banyak Foto -->
                            <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-[10px] font-extrabold flex items-center gap-1 shadow-sm border border-white/20">
                                <svg class="w-3 h-3 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>4 Foto</span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent flex flex-col justify-end p-3.5 group-hover:from-slate-950/90 transition-colors">
                                <span class="self-start px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white shadow-2xs">Dokumentasi Logistik</span>
                                <p class="text-xs font-bold text-white mt-1 leading-snug">Pengiriman Hangat Tepat Waktu</p>
                                <div class="mt-2 flex items-center gap-1 text-[11px] font-bold text-amber-300 group-hover:text-amber-200">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Buka Galeri &amp; Geser Foto &rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Di Samping Kanannya: Card Jam Armada, Total Porsi, Status & Daftar Tujuan -->
                    <div class="md:col-span-7 lg:col-span-8 flex flex-col justify-between gap-3">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">JAM BERANGKAT ARMADA</div>
                                <div class="text-xs sm:text-sm font-extrabold text-indigo-900 mt-0.5">
                                    09.15 WIB
                                </div>
                            </div>
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL DIKIRIM</div>
                                <div class="text-xs sm:text-sm font-extrabold text-slate-900 mt-0.5">
                                    {{ number_format($porsiTerdistribusi, 0, ',', '.') }} Porsi
                                </div>
                            </div>
                            <div class="col-span-2 sm:col-span-1 bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">STATUS PENGIRIMAN</div>
                                <div class="text-xs sm:text-sm font-extrabold text-emerald-700 mt-0.5 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span>Diterima Sekolah</span>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Sasaran Distribusi Siklus Ini -->
                        <div class="bg-white border border-slate-200/80 rounded-xl overflow-hidden shadow-2xs flex-grow">
                            <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700">Tujuan Sekolah Pengiriman Siklus Ini</span>
                                <span class="text-[11px] font-semibold text-slate-500">{{ count($sekolahList) }} Lokasi</span>
                            </div>
                            <div class="divide-y divide-slate-100 max-h-48 overflow-y-auto">
                                @foreach($sekolahList as $s)
                                    <div class="px-3.5 py-2.5 flex items-center justify-between gap-2.5 text-xs">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                            <span class="font-bold text-slate-900 truncate">{{ $s['nama'] }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-indigo-50 text-indigo-700 shrink-0">
                                                {{ $s['tipe'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="font-extrabold text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md text-[11px]">
                                                {{ $s['porsi'] }} Porsi
                                            </span>
                                            <span class="text-emerald-700 font-bold text-[10px] bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded">
                                                Terkirim
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- DAFTAR SEKOLAH YANG DIPASOK (Clean Adaptive Card Grid) -->
    <section class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-4 sm:p-6 lg:p-7 shadow-xs space-y-4">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                Daftar Sekolah yang Dipasok
            </h2>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100/70 text-blue-700 ml-1">
                {{ count($sekolahList) }} Sekolah
            </span>
        </div>
        <p class="text-xs text-slate-500 sm:pl-8">
            Alokasi sasaran porsi harian makanan bergizi gratis pada sekolah binaan {{ $sppg->nama_sppg ?? 'Dapur SPPG' }}
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3.5 pt-1">
            @foreach($sekolahList as $index => $item)
                <div class="bg-slate-50/70 hover:bg-blue-50/40 border border-slate-200/80 hover:border-blue-200 rounded-2xl p-3 sm:px-4 sm:py-3.5 flex items-center justify-between transition-all group shadow-2xs hover:-translate-y-0.5">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <span class="w-7 h-7 rounded-full bg-[#071E49] text-white flex items-center justify-center text-xs font-black shrink-0">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div class="min-w-0">
                            <span class="font-bold text-slate-800 text-xs sm:text-sm truncate block group-hover:text-blue-900 transition-colors">
                                {{ $item['nama'] }}
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-black {{ $item['tipe'] === 'SMP' ? 'bg-purple-100 text-purple-700' : ($item['tipe'] === 'SMA' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700') }} inline-block mt-0.5">
                                {{ $item['tipe'] }}
                            </span>
                        </div>
                    </div>
                    <div class="shrink-0 ml-2 sm:ml-3">
                        <span class="px-2.5 sm:px-3 py-1 rounded-xl bg-white border border-slate-200/80 text-xs font-extrabold text-slate-800 shadow-2xs">
                            {{ $item['porsi'] }} Porsi
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Total Kebutuhan Porsi Siswa -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-end gap-2 text-xs text-slate-500">
            <span class="font-semibold text-slate-600">Total Kebutuhan Porsi Siswa:</span>
            <span class="font-black text-slate-900 text-sm sm:text-base">
                {{ number_format($kapasitasHarian, 0, ',', '.') }} Porsi / Hari
            </span>
        </div>
    </section>

    <!-- TIM PENGELOLA DAPUR & KONTAK OPERASIONAL (Priority on Phone & Dynamic on iPad/Desktop) -->
    <section id="kontak-operasional" class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-4 sm:p-6 lg:p-7 shadow-xs space-y-4 sm:space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                        Tim Pengelola Dapur & Kontak Operasional
                    </h2>
                </div>
                <p class="text-xs text-slate-500 mt-1 sm:pl-8">
                    Pengelola resmi operasional harian {{ $sppg->nama_sppg ?? 'Dapur SPPG' }}
                </p>
            </div>
            <div class="shrink-0 self-start sm:self-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Layanan Siaga Dapur</span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 lg:gap-7 items-center pt-1">
            <!-- Kolom Info Kontak & Tim (Diutamakan di HP & Tablet) -->
            <div class="md:col-span-8 lg:col-span-8 space-y-3.5 order-2 md:order-1">
                <!-- Manajer Operasional SPPG -->
                <div class="bg-white border border-slate-200/90 rounded-2xl p-3.5 sm:p-4 flex items-center gap-3.5 sm:gap-4 shadow-2xs hover:-translate-y-0.5 transition-transform">
                    <div class="w-10 h-10 rounded-full bg-[#071E49] text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] sm:text-[11px] font-black tracking-wider text-blue-700 uppercase">
                            MANAJER OPERASIONAL SPPG
                        </div>
                        <div class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5">
                            {{ $managerName }}
                        </div>
                    </div>
                </div>

                <!-- Nutrisionis / Ahli Gizi -->
                <div class="bg-white border border-slate-200/90 rounded-2xl p-3.5 sm:p-4 flex items-center gap-3.5 sm:gap-4 shadow-2xs hover:-translate-y-0.5 transition-transform">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] sm:text-[11px] font-black tracking-wider text-emerald-700 uppercase">
                            NUTRISIONIS / AHLI GIZI
                        </div>
                        <div class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5">
                            {{ $nutritionistName }}
                        </div>
                    </div>
                </div>

                <!-- Dark Navy Contact Banner (Touch-friendly 44px+ on HP) -->
                <div class="bg-[#071E49] rounded-2xl p-4 sm:p-5 lg:p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-5 shadow-xs">
                    <!-- Kiri: Judul & Deskripsi Kontak -->
                    <div class="flex items-start gap-3 sm:gap-3.5 max-w-sm">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/10 border border-white/20 text-cyan-300 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm sm:text-base font-bold text-white leading-tight">Kontak Layanan Operasional Dapur</div>
                            <div class="text-xs text-blue-200 mt-1 leading-relaxed">
                                Hubungi tim dapur untuk konfirmasi kedatangan porsi atau koordinasi pengantaran sekolah.
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Touch-Friendly Call & Email Actions -->
                    <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 w-full md:w-auto shrink-0 md:min-w-[210px]">
                        <!-- Badge Nomor Telepon -->
                        <a href="tel:{{ preg_replace('/[^0-9]/', '', $sppg->no_telepon ?? '08776537789') }}" class="touch-target-44 inline-flex items-center justify-center md:justify-start gap-2.5 px-4 py-2.5 rounded-xl text-xs font-black bg-white text-slate-900 hover:bg-slate-100 active:scale-95 transition shadow-2xs group">
                            <svg class="w-4 h-4 text-blue-600 group-hover:scale-110 transition-transform shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>{{ $sppg->no_telepon ?? '0877-6537-789' }}</span>
                        </a>

                        <!-- Badge Email -->
                        @php
                            $contactEmail = $sppg->email_sppg ?? ($sppg->user?->email ?? 'kontak.sppg@lensambg.id');
                        @endphp
                        <a href="mailto:{{ $contactEmail }}" class="touch-target-44 inline-flex items-center justify-center md:justify-start gap-2.5 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 active:scale-95 text-white border border-white/20 transition group">
                            <svg class="w-4 h-4 text-slate-300 group-hover:scale-110 transition-transform shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="truncate max-w-[200px]">{{ $contactEmail }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kolom Maskot yay.png (Gambar 1:1 Bersih & Proporsional) -->
            <div class="md:col-span-4 lg:col-span-4 flex items-center justify-center text-center order-1 md:order-2 py-1 md:py-0">
                <img 
                    src="{{ asset('images/yay.png') }}" 
                    alt="Maskot Tim Siaga" 
                    class="w-36 sm:w-48 md:w-52 lg:w-60 aspect-square object-contain hover:scale-105 active:scale-95 transition-transform duration-300 ease-out cursor-pointer select-none"
                    onerror="this.onerror=null; this.src='{{ asset('images/yay-trimmed.png') }}';"
                >
            </div>
        </div>
    </section>

    <!-- LIGHTBOX / POPUP GALERI FOTO TAHAPAN (Touch Swipe & Keyboard Friendly) -->
    <div id="photo-gallery-modal" class="fixed inset-0 z-[100] bg-slate-950/95 backdrop-blur-md hidden flex-col justify-between p-3 sm:p-5 lg:p-6 transition-opacity duration-300 select-none" role="dialog" aria-modal="true" aria-label="Galeri Foto Aktivitas">
        <!-- Top Utility Bar Modal -->
        <div class="flex items-center justify-between pb-3 border-b border-white/10 max-w-5xl mx-auto w-full shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <span id="modal-stage-badge" class="px-2.5 py-1 rounded-md text-xs font-black shadow-xs bg-blue-600 text-white shrink-0">
                    Tahap 01
                </span>
                <div class="min-w-0">
                    <h3 id="modal-stage-title" class="text-xs sm:text-sm font-bold text-white truncate">Dokumentasi Bahan Segar Datang</h3>
                    <div id="modal-photo-counter" class="text-[11px] text-slate-400">Foto 1 dari 4</div>
                </div>
            </div>
            <button 
                type="button" 
                onclick="closePhotoModal()"
                class="touch-target-44 p-2 rounded-xl bg-white/10 hover:bg-white/20 active:scale-90 text-white transition flex items-center justify-center cursor-pointer"
                title="Tutup Galeri (Esc)"
                aria-label="Tutup Galeri"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Main Slide View (Gambar + Tombol Geser) -->
        <div id="modal-slide-area" class="relative flex items-center justify-center flex-grow max-w-4xl mx-auto w-full my-auto py-3 px-2">
            <!-- Tombol Navigasi Kiri (Prev) -->
            <button 
                type="button" 
                onclick="prevModalPhoto(event)"
                class="touch-target-44 absolute left-0 sm:left-2 top-1/2 -translate-y-1/2 z-20 w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-slate-900/80 hover:bg-slate-900 active:scale-90 border border-white/20 text-white flex items-center justify-center shadow-lg transition backdrop-blur-xs cursor-pointer"
                title="Foto Sebelumnya"
                aria-label="Foto Sebelumnya"
            >
                <svg class="w-6 h-6 -translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Foto Utama -->
            <div class="relative max-h-[58vh] sm:max-h-[66vh] max-w-full flex items-center justify-center overflow-hidden rounded-2xl bg-black/40">
                <img id="modal-main-img" src="" alt="Dokumentasi Proses" class="max-h-[58vh] sm:max-h-[66vh] w-auto max-w-full object-contain rounded-xl shadow-2xl transition-all duration-200">
            </div>

            <!-- Tombol Navigasi Kanan (Next) -->
            <button 
                type="button" 
                onclick="nextModalPhoto(event)"
                class="touch-target-44 absolute right-0 sm:right-2 top-1/2 -translate-y-1/2 z-20 w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-slate-900/80 hover:bg-slate-900 active:scale-90 border border-white/20 text-white flex items-center justify-center shadow-lg transition backdrop-blur-xs cursor-pointer"
                title="Foto Selanjutnya"
                aria-label="Foto Selanjutnya"
            >
                <svg class="w-6 h-6 translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Bottom Bar (Keterangan Foto & Thumbnail Geser) -->
        <div class="max-w-4xl mx-auto w-full border-t border-white/10 pt-3 space-y-2 shrink-0">
            <div class="text-center sm:text-left">
                <div id="modal-photo-title" class="text-xs sm:text-sm font-black text-white">Judul Foto</div>
                <div id="modal-photo-desc" class="text-[11px] sm:text-xs text-slate-300 mt-0.5 line-clamp-2">Deskripsi Foto</div>
            </div>

            <!-- Thumbnail Bar -->
            <div id="modal-thumbnails" class="flex items-center justify-center sm:justify-start gap-2.5 overflow-x-auto py-1">
                <!-- Diisi secara dinamis lewat JS -->
            </div>
            
            <div class="text-center text-[10px] text-slate-400 font-medium pb-0.5">
                💡 Geser layar (swipe) atau tekan panah kiri/kanan. Tekan <kbd class="px-1.5 py-0.5 bg-white/10 rounded text-slate-200">Esc</kbd> untuk menutup.
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    /**
     * Logika Galeri Foto Pop-Up (Lightbox Modal yang Bisa Digeser)
     */
    let currentModalStage = 1;
    let currentModalIndex = 0;

    const galleryData = {
        1: {
            badgeText: 'Tahap 01',
            badgeClass: 'bg-blue-600 text-white',
            stageTitle: 'Dokumentasi Bahan Segar Datang',
            photos: [
                {
                    url: 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=1200&q=85',
                    title: 'Sortir Sayur & Pangan Segar Petani Mitra',
                    desc: 'Penerimaan sayuran hijau, tahu, beras, dan komoditas lokal langsung dari mitra petani terverifikasi BGN.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1610348725531-843dff563e2c?auto=format&fit=crop&w=1200&q=85',
                    title: 'Pemeriksaan Kualitas & Kesegaran Telur',
                    desc: 'Uji kebersihan cangkang dan berat telur sesuai spesifikasi gizi per porsi anak sekolah.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1597362925123-77861d3fbac7?auto=format&fit=crop&w=1200&q=85',
                    title: 'Pencucian Higienis Air Mengalir Berstandar',
                    desc: 'Pencucian 3 tahap dengan air mengalir berstandar sanitasi guna melenyapkan residu dan kotoran.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1574484284002-952d92456975?auto=format&fit=crop&w=1200&q=85',
                    title: 'Penimbangan Digital & Suhu Bahan Dingin',
                    desc: 'Pencatatan akurat berat bersih dan suhu bahan pangan segar sebelum masuk ke tempat penyimpanan khusus dapur.'
                }
            ]
        },
        2: {
            badgeText: 'Tahap 02',
            badgeClass: 'bg-amber-600 text-white',
            stageTitle: 'Dokumentasi Olah Masak Matang',
            photos: [
                {
                    url: 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=1200&q=85',
                    title: 'Pemasakan Higienis Suhu Titik Didih >85°C',
                    desc: 'Pengolahan menu aktif dalam wadah stainless steel food-grade bersuhu tinggi guna memastikan bebas kuman.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1577106263724-2c8e03bfe9cf?auto=format&fit=crop&w=1200&q=85',
                    title: 'Juru Masak Bersertifikat & APD Lengkap',
                    desc: 'Seluruh staf pengolah wajib mengenakan masker, hairnet, apron, dan sarung tangan steril sesuai SOP sanitasi dapur.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=1200&q=85',
                    title: 'Pemantauan Suhu Inti Masakan Matang',
                    desc: 'Pengecekan rutin menggunakan digital probe thermo untuk menjamin kematangan merata hingga bagian terdalam makanan.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1507048331197-7d4ac70811cf?auto=format&fit=crop&w=1200&q=85',
                    title: 'Dapur Stainless Steel Higienis Bebas Kontaminasi',
                    desc: 'Area pemisahan zona basah dan matang demi pencegahan kontaminasi silang secara terstandarisasi.'
                }
            ]
        },
        3: {
            badgeText: 'Tahap 03',
            badgeClass: 'bg-emerald-600 text-white',
            stageTitle: 'Dokumentasi Cek Gizi & Pack',
            photos: [
                {
                    url: 'https://images.unsplash.com/photo-1576867757603-05b134ebc379?auto=format&fit=crop&w=1200&q=85',
                    title: 'Uji Organoleptik Rasa, Aroma & Tekstur',
                    desc: 'Nutrisionis dan ahli gizi bersertifikat mencicipi serta memverifikasi cita rasa dan kesegaran hidangan.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1584744982491-665216d95f8b?auto=format&fit=crop&w=1200&q=85',
                    title: 'Penimbangan Gramatur Porsi Siswa Presisi',
                    desc: 'Porsi karbohidrat, protein nabati/hewani, sayur, dan buah ditimbang presisi sesuai standar AKG Generasi Emas BGN.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?auto=format&fit=crop&w=1200&q=85',
                    title: 'Penyegelan Ompreng Stainless Steel Food Grade',
                    desc: 'Wadah makanan bersegel rapat anti-tumpah menjaga kehangatan dan kebersihan hingga disantap siswa di sekolah.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=1200&q=85',
                    title: 'Labeling Jam Batas Konsumsi Aman & Batch ID',
                    desc: 'Setiap kontainer diberi label jam konsumsi maksimal (4 jam) dan kode batch untuk ketertelusuran penuh.'
                }
            ]
        },
        4: {
            badgeText: 'Tahap 04',
            badgeClass: 'bg-indigo-600 text-white',
            stageTitle: 'Dokumentasi Meluncur ke Sekolah',
            photos: [
                {
                    url: 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1200&q=85',
                    title: 'Pemuatan ke Box Thermal Insulasi Suhu Hangat',
                    desc: 'Makanan disusun rapi ke dalam box berinsulasi khusus rantai hangat sebelum dinaikkan ke armada distribusi.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1580674684081-7617fbf3d745?auto=format&fit=crop&w=1200&q=85',
                    title: 'Armada Siaga & Pengawalan Rute Cepat',
                    desc: 'Kendaraan distribusi bergerak sesuai jadwal ketat untuk tiba 30 menit sebelum jam istirahat sekolah.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1200&q=85',
                    title: 'Serah Terima & Pengecekan Suhu di Sekolah',
                    desc: 'Guru penanggung jawab MBG memeriksa segel dan menandatangani berita acara penerimaan digital.'
                },
                {
                    url: 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=1200&q=85',
                    title: 'Siswa Menikmati Santap Bergizi Gratis',
                    desc: 'Anak-anak sekolah menyantap menu bergizi seimbang dengan gembira dan penuh energi.'
                }
            ]
        }
    };

    function openPhotoModal(stageNum, photoIndex = 0) {
        currentModalStage = stageNum;
        currentModalIndex = photoIndex;

        const modal = document.getElementById('photo-gallery-modal');
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        renderModalPhoto();
    }

    function closePhotoModal() {
        const modal = document.getElementById('photo-gallery-modal');
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function renderModalPhoto() {
        const stage = galleryData[currentModalStage];
        if (!stage || !stage.photos.length) return;

        const photos = stage.photos;
        if (currentModalIndex < 0) currentModalIndex = photos.length - 1;
        if (currentModalIndex >= photos.length) currentModalIndex = 0;

        const photo = photos[currentModalIndex];

        // Header
        const badgeElem = document.getElementById('modal-stage-badge');
        badgeElem.className = 'px-2.5 py-1 rounded-md text-xs font-black shadow-xs ' + stage.badgeClass;
        badgeElem.textContent = stage.badgeText;

        document.getElementById('modal-stage-title').textContent = stage.stageTitle;
        document.getElementById('modal-photo-counter').textContent = `Foto ${currentModalIndex + 1} dari ${photos.length}`;

        // Foto Utama
        const mainImg = document.getElementById('modal-main-img');
        mainImg.src = photo.url;
        mainImg.alt = photo.title;

        // Caption
        document.getElementById('modal-photo-title').textContent = photo.title;
        document.getElementById('modal-photo-desc').textContent = photo.desc;

        // Render Thumbnails
        const thumbContainer = document.getElementById('modal-thumbnails');
        thumbContainer.innerHTML = '';
        photos.forEach((p, idx) => {
            const thumb = document.createElement('button');
            thumb.type = 'button';
            thumb.title = p.title;
            thumb.className = `w-12 h-12 sm:w-14 sm:h-14 rounded-xl overflow-hidden border-2 transition-all shrink-0 cursor-pointer ${idx === currentModalIndex ? 'border-amber-400 ring-2 ring-amber-400/50 scale-105' : 'border-white/20 opacity-60 hover:opacity-100'}`;
            thumb.innerHTML = `<img src="${p.url}" alt="${p.title}" class="w-full h-full object-cover">`;
            thumb.onclick = () => {
                currentModalIndex = idx;
                renderModalPhoto();
            };
            thumbContainer.appendChild(thumb);
        });
    }

    function prevModalPhoto(e) {
        if (e) e.stopPropagation();
        currentModalIndex--;
        renderModalPhoto();
    }

    function nextModalPhoto(e) {
        if (e) e.stopPropagation();
        currentModalIndex++;
        renderModalPhoto();
    }

    // Touch Swipe Gesture pada Modal Slide Area (Mobile First UX)
    let touchStartX = 0;
    let touchEndX = 0;
    const slideArea = document.getElementById('modal-slide-area');
    if (slideArea) {
        slideArea.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        slideArea.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            const threshold = 35;
            if (touchEndX < touchStartX - threshold) {
                nextModalPhoto();
            } else if (touchEndX > touchStartX + threshold) {
                prevModalPhoto();
            }
        }, { passive: true });
    }

    // Keyboard Navigation (Esc to close, Arrow keys to slide)
    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('photo-gallery-modal');
        if (!modal || modal.classList.contains('hidden')) return;

        if (e.key === 'Escape') {
            closePhotoModal();
        } else if (e.key === 'ArrowRight') {
            nextModalPhoto();
        } else if (e.key === 'ArrowLeft') {
            prevModalPhoto();
        }
    });

    /**
     * Logika Akordion Interaktif untuk Aktivitas Siklus (Membuka ke Bawah)
     */
    let currentOpenTahap = null;

    const tahapMetadata = {
        1: {
            title: 'Rincian Bahan Segar Datang',
            subtitle: 'Penerimaan & Sortir Bahan Baku Subuh dari Mitra Lokal',
            badgeClass: 'bg-blue-600 text-white',
            badgeText: 'Tahap 01'
        },
        2: {
            title: 'Rincian Olah Masak Matang',
            subtitle: 'Pengolahan Menu Higienis Sesuai Standar Suhu Kematangan',
            badgeClass: 'bg-amber-600 text-white',
            badgeText: 'Tahap 02'
        },
        3: {
            title: 'Rincian Cek Gizi & Pack',
            subtitle: 'Pemeriksaan Organoleptik Rasa, Fisik & Sertifikasi Ahli Gizi',
            badgeClass: 'bg-emerald-600 text-white',
            badgeText: 'Tahap 03'
        },
        4: {
            title: 'Rincian Meluncur ke Sekolah',
            subtitle: 'Distribusi Logistik Rantai Hangat Menuju Seluruh Sekolah',
            badgeClass: 'bg-indigo-600 text-white',
            badgeText: 'Tahap 04'
        }
    };

    function toggleTahapDetail(num) {
        const container = document.getElementById('tahap-drawer-container');
        if (!container) return;

        // Jika mengklik tahap yang sedang terbuka, tutup laci
        if (currentOpenTahap === num) {
            closeTahapDrawer();
            return;
        }

        // Buka tahap yang dipilih
        openTahapDrawer(num);
    }

    function openTahapDrawer(num) {
        const container = document.getElementById('tahap-drawer-container');
        if (!container) return;

        currentOpenTahap = num;
        container.classList.remove('hidden');

        // Update Header Drawer
        const meta = tahapMetadata[num] || tahapMetadata[1];
        document.getElementById('drawer-title').textContent = meta.title;
        document.getElementById('drawer-subtitle').textContent = meta.subtitle;
        
        const badgeElem = document.getElementById('drawer-badge-tahap');
        badgeElem.className = 'px-2.5 py-1 rounded-md text-xs font-black shadow-2xs ' + meta.badgeClass;
        badgeElem.textContent = meta.badgeText;

        // Tampilkan hanya konten tahap aktif
        for (let i = 1; i <= 4; i++) {
            const content = document.getElementById('content-tahap-' + i);
            const card = document.getElementById('card-tahap-' + i);
            const btnText = document.getElementById('btn-text-' + i);
            const chevron = document.getElementById('chevron-' + i);
            const tabBtn = document.getElementById('tab-btn-' + i);

            if (i === num) {
                content?.classList.remove('hidden');
                
                // Highlight card aktif
                if (card) {
                    card.classList.add('is-active', 'ring-2', 'ring-[#071E49]', 'bg-blue-50/70');
                }
                if (btnText) btnText.textContent = 'Tutup';
                if (chevron) chevron.classList.add('rotate-180');
                if (tabBtn) {
                    tabBtn.className = 'px-2.5 py-1 rounded-lg bg-[#071E49] text-white shadow-2xs transition min-h-[36px] sm:min-h-0';
                }
            } else {
                content?.classList.add('hidden');
                
                if (card) {
                    card.classList.remove('is-active', 'ring-2', 'ring-[#071E49]', 'bg-blue-50/70');
                }
                if (btnText) btnText.textContent = 'Rincian';
                if (chevron) chevron.classList.remove('rotate-180');
                if (tabBtn) {
                    tabBtn.className = 'px-2.5 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition min-h-[36px] sm:min-h-0';
                }
            }
        }

        // Smooth scroll to container on all viewports for seamless transition
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function closeTahapDrawer() {
        const container = document.getElementById('tahap-drawer-container');
        if (!container) return;

        container.classList.add('hidden');
        currentOpenTahap = null;

        for (let i = 1; i <= 4; i++) {
            const content = document.getElementById('content-tahap-' + i);
            const card = document.getElementById('card-tahap-' + i);
            const btnText = document.getElementById('btn-text-' + i);
            const chevron = document.getElementById('chevron-' + i);
            const tabBtn = document.getElementById('tab-btn-' + i);

            content?.classList.add('hidden');
            if (card) card.classList.remove('is-active', 'ring-2', 'ring-[#071E49]', 'bg-blue-50/70');
            if (btnText) btnText.textContent = 'Rincian';
            if (chevron) chevron.classList.remove('rotate-180');
            if (tabBtn) tabBtn.className = 'px-2.5 py-1 rounded-lg text-slate-600 transition min-h-[36px] sm:min-h-0';
        }
    }
</script>
@endpush
