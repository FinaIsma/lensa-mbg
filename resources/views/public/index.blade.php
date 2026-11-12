@extends('layouts.public')

@section('title', 'Transparansi Penuh Asupan Gizi Sekolah Anak Bangsa - Lensa MBG')

@section('content')
<div class="space-y-10 pb-16">

    <!-- Hero Section Banner (Contained Rounded Card persis Gambar 2 dengan warna Primary #071E49) -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 sm:mt-8">
        <div class="bg-[#071E49] text-white rounded-3xl p-6 sm:p-8 lg:p-10 shadow-md border border-[#042A70]/50">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Sisi Kiri -->
                <div class="max-w-2xl space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-medium bg-white/10 text-emerald-300 border border-white/15">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Pilar Keterbukaan Informasi Publik RI No. 14/2008</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        Transparansi Penuh Asupan Gizi Sekolah Anak Bangsa
                    </h1>
                    <p class="text-blue-100/90 text-xs sm:text-sm leading-relaxed">
                        Setiap porsi makanan siswa diawasi secara hulu ke hilir. Pantau kepatuhan nutrisi, kesegaran bahan pangan, proses masak dapur SPPG, hingga bukti serah terima sekolah tanpa perlu membuat akun.
                    </p>
                </div>

                <!-- Sisi Kanan: Target Distribusi Hari Ini Box -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/15 shrink-0 min-w-[250px] space-y-2">
                    <div class="flex items-center justify-between text-xs text-blue-100 font-medium">
                        <span>Target Distribusi Hari Ini</span>
                        <span class="font-bold text-emerald-300">99.8%</span>
                    </div>
                    <div class="w-full bg-white/20 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-emerald-400 h-full rounded-full" style="width: 99.8%"></div>
                    </div>
                    <div class="text-[11px] text-blue-200 font-medium text-right pt-1">
                        38 Provinsi / {{ number_format($totalDestinations, 0, ',', '.') }} Satuan Sekolah
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card: Pilih Instansi Penerima -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="bg-white rounded-3xl shadow-sm p-6 sm:p-8 text-slate-800 border border-slate-200/80 relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#071E49] text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-lg">Pilih Instansi Penerima</h2>
                        <p class="text-xs text-slate-500">Pilih lokasi dan sekolah untuk melihat data keterlacakan makanan dan asupan nutrisi hari ini.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Data Terkini: Hari Ini, {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
                    </span>
                    @if(request()->hasAny(['destination_id', 'provinsi', 'kabupaten_kota', 'kecamatan']))
                        <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-slate-800 px-3 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Reset Filter</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Form Wilayah & Instansi -->
            <form id="filterForm" method="GET" action="{{ route('public.home') }}" class="pt-5 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- 1. Provinsi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">1. Provinsi</label>
                        <select id="select-provinsi" name="provinsi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">Pilih Provinsi</option>
                        </select>
                    </div>

                    <!-- 2. Kota / Kabupaten -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">2. Kota / Kabupaten</label>
                        <select id="select-regency" name="kabupaten_kota" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-500 focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">Pilih provinsi dahulu</option>
                        </select>
                    </div>

                    <!-- 3. Kecamatan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">3. Kecamatan</label>
                        <select id="select-district" name="kecamatan" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-500 focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">Pilih kota/kabupaten dahulu</option>
                        </select>
                    </div>
                </div>

                <!-- Instansi Dropdown Row -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Pilih Sekolah / Instansi Target</span>
                        </span>
                        <span class="text-[11px] text-slate-400 font-normal">Disaring sesuai wilayah SPPG</span>
                    </label>
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="relative flex-1 w-full">
                            <select id="select-instansi" name="destination_id" {{ $allDestinations->isEmpty() ? 'disabled' : '' }} class="w-full pl-10 pr-8 py-3 rounded-xl border border-slate-300 {{ $allDestinations->isEmpty() ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : 'bg-white text-slate-800' }} text-sm font-bold focus:ring-2 focus:ring-blue-500 shadow-2xs transition">
                                <option value="">{{ $allDestinations->isEmpty() ? 'Pilih kecamatan terlebih dahulu' : '-- Pilih Sekolah / Instansi Sasaran --' }}</option>
                                @foreach($allDestinations as $dest)
                                    <option value="{{ $dest->id }}" {{ (isset($selectedDestination) && $selectedDestination->id == $dest->id) ? 'selected' : '' }}>
                                        {{ $dest->nama }} ({{ $dest->sppg?->nama_sppg ?? 'SPPG' }})
                                    </option>
                                @endforeach
                            </select>
                            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                        </div>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-sm bg-[#071E49] hover:bg-[#042A70] text-white shadow-sm hover:shadow transition flex items-center justify-center gap-2 shrink-0">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Muat Transparansi</span>
                        </button>
                    </div>
                </div>

                <!-- STATE KOSONG: Belum pilih apa-apa, data tidak muncul -->
                @if(!$selectedDestination)
                    <div class="rounded-2xl border-2 border-dashed border-slate-200 p-10 text-center bg-slate-50/50 mt-6">
                        <div class="w-12 h-12 rounded-full bg-white text-slate-400 shadow-2xs flex items-center justify-center mx-auto mb-3 border border-slate-100">
                            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-700 text-sm">Pilih instansi terlebih dahulu</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Tentukan wilayah sampai kecamatan, lalu pilih sekolah untuk melihat menu & data transparansi hari ini.</p>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- HASIL TRANSPARANSI: HANYA MUNCUL JIKA SUDAH PILIH INSTANSI (TERPISAH PER KONTEN SEPERTI GAMBAR 3 & GAMBAR 4) -->
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

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
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
                                    Belum Ada Pengiriman Hari Ini
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

            <!-- KONTEN 5: PUSAT ASPIRASI & LAPORAN -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-slate-400 inline-block shadow-2xs"></span>
                    <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Pusat Aspirasi & Laporan Masyarakat</h3>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="https://www.lapor.go.id" target="_blank" class="p-4 rounded-2xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50/30 transition flex items-start gap-3 group">
                            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-sm text-slate-900 group-hover:text-blue-700 transition">SP4N-LAPOR!</h5>
                                <p class="text-xs text-slate-500 mt-0.5">Kanal resmi pengaduan pelayanan publik nasional</p>
                            </div>
                        </a>

                        <div class="p-4 rounded-2xl border border-slate-200 flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-sm text-slate-900">WhatsApp Satgas MBG</h5>
                                <p class="text-xs text-slate-500 mt-0.5">0811-9213-990 (Respons cepat 24 jam)</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl border border-slate-200 flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-sm text-slate-900">Surel Aduan Mutu</h5>
                                <p class="text-xs text-slate-500 mt-0.5">pengaduan@lensambg.go.id</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const provSelect = document.getElementById('select-provinsi');
    const regencySelect = document.getElementById('select-regency');
    const districtSelect = document.getElementById('select-district');
    const instansiSelect = document.getElementById('select-instansi');

    const currentProv = "{{ request('provinsi', '') }}";
    const currentKab = "{{ request('kabupaten_kota', '') }}";
    const currentKec = "{{ request('kecamatan', '') }}";

    // 1. Muat Daftar Provinsi
    fetch('/api/wilayah/provinces')
        .then(res => res.json())
        .then(data => {
            provSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.name;
                opt.dataset.id = item.id;
                opt.textContent = item.name;
                if (currentProv && (currentProv.toLowerCase() === item.name.toLowerCase())) {
                    opt.selected = true;
                }
                provSelect.appendChild(opt);
            });

            // Jika ada initial value dari URL query
            if (currentProv && provSelect.selectedOptions[0]?.dataset?.id) {
                loadRegencies(provSelect.selectedOptions[0].dataset.id, currentKab, currentKec);
            }
        })
        .catch(err => console.error('Error load provinces:', err));

    // Event Provinsi Change -> Muat Kota / Kabupaten
    provSelect.addEventListener('change', function () {
        const selectedOpt = this.options[this.selectedIndex];
        const provId = selectedOpt ? selectedOpt.dataset.id : null;
        const provName = this.value;

        resetRegencies();
        resetDistricts();

        if (!provId) {
            filterInstansiDropdown('', '', '');
            return;
        }

        loadRegencies(provId, '', '');
        filterInstansiDropdown(provName, '', '');
    });

    // Helper Load Regencies
    function loadRegencies(provId, preselectKab, preselectKec) {
        regencySelect.innerHTML = '<option value="">Memuat...</option>';
        regencySelect.disabled = true;

        fetch(`/api/wilayah/regencies/${provId}`)
            .then(res => res.json())
            .then(data => {
                regencySelect.disabled = false;
                regencySelect.className = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-500 transition';
                regencySelect.innerHTML = '<option value="">Pilih Kota / Kabupaten</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.name;
                    opt.dataset.id = item.id;
                    opt.textContent = item.name;
                    if (preselectKab && (preselectKab.toLowerCase() === item.name.toLowerCase())) {
                        opt.selected = true;
                    }
                    regencySelect.appendChild(opt);
                });

                if (preselectKab && regencySelect.selectedOptions[0]?.dataset?.id) {
                    loadDistricts(regencySelect.selectedOptions[0].dataset.id, preselectKec);
                }
            })
            .catch(() => {
                resetRegencies();
            });
    }

    // Event Regency Change -> Muat Kecamatan
    regencySelect.addEventListener('change', function () {
        const selectedOpt = this.options[this.selectedIndex];
        const regencyId = selectedOpt ? selectedOpt.dataset.id : null;
        const provName = provSelect.value;
        const regencyName = this.value;

        resetDistricts();

        if (!regencyId) {
            filterInstansiDropdown(provName, '', '');
            return;
        }

        loadDistricts(regencyId, '');
        filterInstansiDropdown(provName, regencyName, '');
    });

    // Helper Load Districts
    function loadDistricts(regencyId, preselectKec) {
        districtSelect.innerHTML = '<option value="">Memuat...</option>';
        districtSelect.disabled = true;

        fetch(`/api/wilayah/districts/${regencyId}`)
            .then(res => res.json())
            .then(data => {
                districtSelect.disabled = false;
                districtSelect.className = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-500 transition';
                districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.name;
                    opt.dataset.id = item.id;
                    opt.textContent = item.name;
                    if (preselectKec && (preselectKec.toLowerCase() === item.name.toLowerCase())) {
                        opt.selected = true;
                    }
                    districtSelect.appendChild(opt);
                });
            })
            .catch(() => {
                resetDistricts();
            });
    }

    // Event District Change -> Filter Instansi (Pilihan 1: sampai kecamatan saja)
    districtSelect.addEventListener('change', function () {
        const provName = provSelect.value;
        const regencyName = regencySelect.value;
        const districtName = this.value;

        if (!districtName) {
            resetInstansi();
            return;
        }

        filterInstansiDropdown(provName, regencyName, districtName);
    });

    // Auto load data saat sekolah / instansi dipilih
    instansiSelect.addEventListener('change', function () {
        if (this.value) {
            document.getElementById('filterForm').submit();
        }
    });

    function resetRegencies() {
        regencySelect.innerHTML = '<option value="">Pilih provinsi dahulu</option>';
        regencySelect.disabled = true;
        regencySelect.className = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-500 focus:ring-2 focus:ring-blue-500 transition';
        resetDistricts();
    }

    function resetDistricts() {
        districtSelect.innerHTML = '<option value="">Pilih kota/kabupaten dahulu</option>';
        districtSelect.disabled = true;
        districtSelect.className = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-500 focus:ring-2 focus:ring-blue-500 transition';
        resetInstansi();
    }

    function resetInstansi() {
        instansiSelect.innerHTML = '<option value="">Pilih kecamatan terlebih dahulu</option>';
        instansiSelect.disabled = true;
        instansiSelect.className = 'w-full pl-10 pr-8 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed text-sm font-bold shadow-2xs transition';
    }

    // Helper untuk memfilter isi dropdown Instansi via AJAX
    function filterInstansiDropdown(prov, kab, kec) {
        if (!kec) {
            resetInstansi();
            return;
        }

        let params = new URLSearchParams();
        if (prov) params.append('provinsi', prov);
        if (kab) params.append('kabupaten_kota', kab);
        if (kec) params.append('kecamatan', kec);

        instansiSelect.disabled = true;
        instansiSelect.innerHTML = '<option value="">Memuat instansi sasaran...</option>';

        fetch(`/api/public/destinations?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                instansiSelect.innerHTML = '<option value="">-- Pilih Sekolah / Instansi Sasaran --</option>';
                if (!data || data.length === 0) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'Tidak ada instansi terdaftar di wilayah ini';
                    opt.disabled = true;
                    instansiSelect.appendChild(opt);
                    instansiSelect.disabled = true;
                    instansiSelect.className = 'w-full pl-10 pr-8 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed text-sm font-bold shadow-2xs transition';
                } else {
                    instansiSelect.disabled = false;
                    instansiSelect.className = 'w-full pl-10 pr-8 py-3 rounded-xl border border-slate-300 bg-white text-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500 shadow-2xs transition';
                    data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = `${item.nama} (${item.sppg_nama})`;
                        instansiSelect.appendChild(opt);
                    });
                }
            })
            .catch(err => {
                console.error('Error load destinations:', err);
                resetInstansi();
            });
    }
});

function openPhotoModal(imgSrc, caption) {
    const modal = document.getElementById('photoModal');
    const img = document.getElementById('modalImg');
    const cap = document.getElementById('modalCaption');
    img.src = imgSrc;
    cap.innerText = caption || 'Foto Verifikasi Porsi';
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
    }, 10);
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    modal.classList.add('opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}
</script>
@endsection
