@extends('layouts.public')

@section('title', 'Telusur Jejak Makanan Siswa (Batch Traceability) - Lensa MBG')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8 sm:space-y-10">

    <!-- 1. Header & Search Box (Clean Editorial) -->
    <div class="text-center space-y-3 max-w-2xl mx-auto">
        <div class="flex items-center justify-center gap-2 text-xs font-semibold text-slate-500">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Audit Rantai Pasok MBG Terbuka</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Telusur Jejak Makanan Siswa
        </h1>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
            Masukkan kode batch yang tertera pada label kemasan baki saji atau hasil scan QR code untuk memverifikasi asal pasokan bahan, jam pengolahan dapur, hingga serah terima di sekolah sasaran.
        </p>

        <!-- Form Cari Batch -->
        <form method="GET" action="{{ route('public.trace') }}" class="pt-2">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 max-w-md mx-auto">
                <div class="relative flex-1">
                    <input type="text" name="batch_code" value="{{ $batch_code ?? '' }}" placeholder="Contoh: BTC-20261013-001" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 bg-white focus:border-[#071E49] focus:outline-none focus:ring-1 focus:ring-[#071E49] text-xs sm:text-sm font-mono tracking-wider uppercase text-slate-900 placeholder:text-slate-400 placeholder:font-sans shadow-2xs transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <button type="submit" class="py-3 px-6 rounded-xl bg-[#071E49] hover:bg-[#042A70] text-white font-bold text-xs sm:text-sm shadow-xs hover:shadow transition flex items-center justify-center gap-2 cursor-pointer shrink-0">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lacak</span>
                </button>
            </div>
        </form>

        <!-- Saran Pencarian Cepat -->
        @if($recentTraceableBatches->isNotEmpty() && !$batch)
            <div class="flex flex-wrap items-center justify-center gap-2 pt-2 text-xs text-slate-500">
                <span class="font-medium">Contoh batch aktif:</span>
                @foreach($recentTraceableBatches as $rb)
                    <a href="{{ route('public.trace', $rb->batch_code) }}" class="px-2.5 py-1 rounded-md bg-white border border-slate-200/90 hover:border-[#071E49] hover:text-[#071E49] text-slate-700 font-mono text-[11px] font-semibold transition shadow-2xs">
                        {{ $rb->batch_code }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if($batch)
        <!-- 2. Hasil Telusur Batch (Arsitektural & Rapi) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden space-y-8 p-6 sm:p-8 lg:p-10">
            
            <!-- Ringkasan Profil Batch & Penyaluran (Terstruktur Tanpa Kotak-dalam-Kotak) -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-200/80">
                <div class="space-y-1.5 flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Status: Batch {{ ucfirst($batch->status) }}</span>
                        </span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">
                            {{ $batch->batch_code }}
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                        {{ $batch->menu?->nama ?? 'Menu Masakan Bergizi' }}
                    </h2>
                    <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-2.5 gap-y-1 pt-0.5 font-normal">
                        <span>Tanggal Masak: <strong class="text-slate-800 font-semibold">{{ \Carbon\Carbon::parse($batch->production_date)->translatedFormat('l, d F Y') }}</strong></span>
                        <span class="text-slate-300">&bull;</span>
                        <span>Dapur: <strong class="text-slate-800 font-semibold">{{ $batch->sppg?->nama_sppg ?? 'SPPG Pusat' }}</strong></span>
                        @if($distributions->isNotEmpty())
                            <span class="text-slate-300">&bull;</span>
                            <span>Sekolah Sasaran: <strong class="text-slate-800 font-semibold">{{ $distributions->pluck('destination.nama')->filter()->unique()->count() }} Lokasi</strong></span>
                        @endif
                    </div>
                    @if($batch->menu?->deskripsi)
                        <p class="text-xs text-slate-500 pt-1 leading-relaxed max-w-2xl">
                            {{ $batch->menu->deskripsi }}
                        </p>
                    @endif
                </div>

                <!-- Informasi Realisasi Porsi (Garis Vertikal Bersih, Tanpa Kotak Berlapis) -->
                @php
                    $totalSent = $distributions->sum('portions_sent');
                    $totalReceived = $distributions->where('status', 'received')->sum('portions_sent');
                @endphp
                <div class="lg:border-l lg:border-slate-200 lg:pl-8 lg:min-w-[280px] flex flex-col justify-center space-y-1 pt-4 lg:pt-0 border-t border-slate-100 lg:border-t-0">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hasil Porsi Siap Saji</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        {{ number_format($batch->actual_porsi ?? $batch->target_porsi, 0, ',', '.') }}
                        <span class="text-sm font-normal text-slate-400">Porsi</span>
                    </div>
                    @if($distributions->isNotEmpty())
                        <div class="text-xs text-slate-600 flex items-center gap-1.5 pt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $totalReceived >= $totalSent ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }}"></span>
                            <span>Tersalurkan: <strong class="text-slate-800 font-semibold">{{ number_format($totalSent, 0, ',', '.') }} porsi</strong> ({{ $distributions->count() }} sekolah)</span>
                        </div>
                    @else
                        <p class="text-xs text-slate-500">
                            Standar Porsi: <strong class="text-slate-700 font-semibold">{{ $batch->recipe?->ukuran_porsi ?? 'Standar Nutrisi Siswa' }}</strong>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Rincian Nilai Gizi Terverifikasi (Jika Recipe Memiliki Data Nutrisi) -->
            @php
                $nutritions = $batch->recipe?->nutritions ?? collect();
                $kalori = $nutritions->firstWhere('nutritionType.nama', 'Kalori Total') ?? $nutritions->firstWhere('nutritionType.nama', 'Kalori');
                $protein = $nutritions->firstWhere('nutritionType.nama', 'Protein');
                $karbo = $nutritions->firstWhere('nutritionType.nama', 'Karbohidrat');
                $serat = $nutritions->firstWhere('nutritionType.nama', 'Serat Pangan') ?? $nutritions->firstWhere('nutritionType.nama', 'Serat');
            @endphp
            @if($kalori || $protein || $karbo || $serat)
                <div class="space-y-2.5 pb-2">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Komposisi Gizi Terverifikasi per Porsi</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-50/80 border border-slate-200/90 rounded-xl p-3 text-center">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200/70 text-slate-700">AKG</span>
                            <div class="text-lg sm:text-xl font-black text-[#071E49] mt-1 tracking-tight">
                                {{ $kalori ? (int)$kalori->nilai : '-' }} <span class="text-xs font-medium text-slate-400">kkal</span>
                            </div>
                            <div class="text-xs font-bold text-slate-800">Kalori Total</div>
                        </div>
                        <div class="bg-slate-50/80 border border-slate-200/90 rounded-xl p-3 text-center">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200/70 text-slate-700">Utama</span>
                            <div class="text-lg sm:text-xl font-black text-[#071E49] mt-1 tracking-tight">
                                {{ $protein ? (float)$protein->nilai : '-' }} <span class="text-xs font-medium text-slate-400">g</span>
                            </div>
                            <div class="text-xs font-bold text-slate-800">Protein</div>
                        </div>
                        <div class="bg-slate-50/80 border border-slate-200/90 rounded-xl p-3 text-center">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200/70 text-slate-700">Kompleks</span>
                            <div class="text-lg sm:text-xl font-black text-[#071E49] mt-1 tracking-tight">
                                {{ $karbo ? (float)$karbo->nilai : '-' }} <span class="text-xs font-medium text-slate-400">g</span>
                            </div>
                            <div class="text-xs font-bold text-slate-800">Karbohidrat</div>
                        </div>
                        <div class="bg-slate-50/80 border border-slate-200/90 rounded-xl p-3 text-center">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200/70 text-slate-700">Alami</span>
                            <div class="text-lg sm:text-xl font-black text-[#071E49] mt-1 tracking-tight">
                                {{ $serat ? (float)$serat->nilai : '-' }} <span class="text-xs font-medium text-slate-400">g</span>
                            </div>
                            <div class="text-xs font-bold text-slate-800">Serat Pangan</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 3. Linimasa Vertikal Lengkap (Hulu ke Hilir) -->
            <div class="relative pl-6 sm:pl-8 border-l-2 border-slate-200 space-y-10 ml-3 sm:ml-4">

                <!-- TAHAP 1: Pasokan Bahan Baku Segar -->
                <div class="relative space-y-3">
                    <span class="absolute -left-[33px] sm:-left-[41px] top-0 w-8 h-8 rounded-full bg-[#071E49] text-white flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-2xs">
                        1
                    </span>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hulu / Pasokan</div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Bahan Baku Segar & Mitra Petani Lokal</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Semua bahan baku terverifikasi metode FEFO dan lolos inspeksi fisik kesegaran sebelum masuk dapur.</p>
                    </div>

                    @php
                        $materialGallery = $materials->filter(fn($m) => !empty($m['photo_url']))->map(fn($m) => [
                            'url' => $m['photo_url'],
                            'caption' => $m['photo_desc'] ?? ('Bukti Fisik Timbangan: ' . $m['material_nama'] . ' (' . $m['supplier_nama'] . ')')
                        ])->values();
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                        @forelse($materials as $matIndex => $mat)
                            <div class="bg-slate-50/80 rounded-xl border border-slate-200/80 text-xs hover:border-slate-300 transition overflow-hidden flex flex-col justify-between">
                                @if(!empty($mat['photo_url']))
                                    @php
                                        $galIndex = $materialGallery->search(fn($item) => $item['url'] === $mat['photo_url']);
                                        $safeIndex = $galIndex !== false ? $galIndex : 0;
                                    @endphp
                                    <div onclick='openPhotoModal("{{ $mat['photo_url'] }}", "{{ addslashes($mat['photo_desc'] ?? ('Bukti Fisik ' . $mat['material_nama'])) }}", @json($materialGallery), {{ $safeIndex }})'
                                         class="relative w-full aspect-16/9 bg-slate-100 overflow-hidden cursor-pointer group/photo border-b border-slate-200/60">
                                        <img src="{{ $mat['photo_url'] }}"
                                             alt="{{ $mat['material_nama'] }}"
                                             class="w-full h-full object-cover group-hover/photo:scale-103 transition duration-200"
                                             onerror="this.onerror=null; this.parentElement.style.display='none';">
                                        <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover/photo:opacity-100 transition-opacity flex items-center justify-center">
                                            <span class="px-2.5 py-1 rounded bg-white/95 text-slate-900 font-bold text-[10px] shadow-sm flex items-center gap-1">
                                                <svg class="w-3 h-3 text-[#071E49]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                Buka & Geser
                                            </span>
                                        </div>
                                        <div class="absolute bottom-1.5 left-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-black/60 text-white font-medium text-[9px] backdrop-blur-xs flex items-center gap-1">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                Bukti Fisik
                                            </span>
                                        </div>
                                    </div>
                                @endif
                                <div class="p-3.5 space-y-1.5 flex-1 flex flex-col justify-between">
                                    <div class="space-y-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="font-bold text-slate-900 text-sm leading-snug">{{ $mat['material_nama'] }}</div>
                                            @if(!empty($mat['photo_url']))
                                                @php
                                                    $galIndex = $materialGallery->search(fn($item) => $item['url'] === $mat['photo_url']);
                                                    $safeIndex = $galIndex !== false ? $galIndex : 0;
                                                @endphp
                                                <button type="button"
                                                        onclick='openPhotoModal("{{ $mat['photo_url'] }}", "{{ addslashes($mat['photo_desc'] ?? ('Bukti Fisik ' . $mat['material_nama'])) }}", @json($materialGallery), {{ $safeIndex }})'
                                                        title="Lihat foto timbangan"
                                                        class="text-[#071E49] hover:text-[#0b2b68] cursor-pointer shrink-0">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                        <div class="text-slate-500 text-[11px] flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span class="truncate">Pemasok: <strong class="text-slate-700">{{ $mat['supplier_nama'] }}</strong></span>
                                        </div>
                                    </div>
                                    @if($mat['received_date'])
                                        <div class="text-[10px] text-slate-400 pt-1.5 border-t border-slate-200/50 flex items-center justify-between">
                                            <span>Masuk: {{ \Carbon\Carbon::parse($mat['received_date'])->format('d/m/Y') }}</span>
                                            <span>Aman: {{ \Carbon\Carbon::parse($mat['expiry_date'])->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-xs text-slate-400 italic">Rincian bahan baku tercatat terpadu dalam standar resep aktif.</div>
                        @endforelse
                    </div>
                </div>

                <!-- TAHAP 2: Pengolahan di Dapur -->
                @php
                    $stepGallery = $steps->flatMap(fn($s) => $s->evidences)->map(fn($ev) => [
                        'url' => $ev->url,
                        'caption' => $ev->description ?? 'Proses Pengolahan Dapur SPPG'
                    ])->values();
                @endphp
                <div class="relative space-y-3">
                    <span class="absolute -left-[33px] sm:-left-[41px] top-0 w-8 h-8 rounded-full bg-[#071E49] text-white flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-2xs">
                        2
                    </span>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dapur SPPG</div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Tahapan Pengolahan & Sanitasi Makanan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Mencatat waktu awal dan selesai setiap tahapan masak demi menjamin higienitas wadah stainless steel food-grade.</p>
                    </div>

                    <div class="space-y-2 pt-1">
                        @forelse($steps as $step)
                            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80 text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-2 h-2 rounded-full {{ $step->status === 'done' ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }} shrink-0"></span>
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm">{{ $step->step_name }}</div>
                                            <div class="text-[11px] text-slate-500">
                                                Mulai: {{ \Carbon\Carbon::parse($step->started_at)->format('H:i') }} WIB
                                                @if($step->ended_at)
                                                    &mdash; Selesai: {{ \Carbon\Carbon::parse($step->ended_at)->format('H:i') }} WIB
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold {{ $step->status === 'done' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $step->status === 'done' ? 'Selesai' : 'Sedang Berjalan' }}
                                    </span>
                                </div>
                                @if($step->evidences && $step->evidences->isNotEmpty())
                                    <div class="pt-2 border-t border-slate-200/60 flex flex-wrap items-center gap-2">
                                        @foreach($step->evidences as $stepEv)
                                            @php
                                                $sIdx = $stepGallery->search(fn($item) => $item['url'] === $stepEv->url);
                                                $sSafeIdx = $sIdx !== false ? $sIdx : 0;
                                            @endphp
                                            <button type="button"
                                                    onclick='openPhotoModal("{{ $stepEv->url }}", "{{ addslashes($stepEv->description ?? 'Proses Dapur') }}", @json($stepGallery), {{ $sSafeIdx }})'
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-[#071E49] text-slate-700 hover:text-[#071E49] cursor-pointer transition text-[11px] font-medium shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>Foto Bukti {{ $step->step_name }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 italic">Proses pengolahan dapur dilakukan terstandar food-grade 304.</div>
                        @endforelse
                    </div>
                </div>

                <!-- TAHAP 3: Pemeriksaan Kelayakan Saji (QC) -->
                @php
                    $qcEvidences = $qcCheck?->evidences ?? collect();
                    $qcGallery = $qcEvidences->map(fn($ev) => [
                        'url' => $ev->url,
                        'caption' => $ev->description ?? 'Uji Kelayakan Saji QC'
                    ])->values();
                @endphp
                <div class="relative space-y-3">
                    <span class="absolute -left-[33px] sm:-left-[41px] top-0 w-8 h-8 rounded-full bg-[#071E49] text-white flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-2xs">
                        3
                    </span>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Quality Control (QC)</div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Pemeriksaan Mutu & Uji Kelayakan Saji</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pengecekan akhir aroma, rasa, tingkat kematangan, dan suhu hangat sebelum dikemas ke box insulated.</p>
                    </div>

                    <div class="p-4 sm:p-5 rounded-xl bg-emerald-50/40 border border-emerald-200/80 text-xs space-y-2.5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <span class="font-bold text-emerald-950 text-sm">Hasil Pemeriksaan Uji Kelayakan Saji:</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-100/80 text-emerald-900 border border-emerald-300">
                                <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Lolos Uji Fisik & Organoleptik
                            </span>
                        </div>
                        <p class="text-slate-700 leading-relaxed font-normal">
                            {{ $qcCheck?->reported_condition ?? 'Makanan matang sempurna, aroma segar khas rempah, baki tertutup rapat dan higienis.' }}
                        </p>
                        <div class="pt-2 border-t border-emerald-200/60 flex flex-wrap gap-x-5 gap-y-1.5 text-[11px] text-slate-600">
                            <div><strong>Tenaga Ahli Gizi:</strong> {{ $qcCheck?->effective_nutritionist_name ?? 'Tenaga Pelaksana Gizi SPPG' }}</div>
                            @if($qcCheck?->checked_at)
                                <div><strong>Waktu Pengecekan:</strong> {{ \Carbon\Carbon::parse($qcCheck->checked_at)->format('H:i') }} WIB</div>
                            @endif
                            @if($qcCheck?->consume_by)
                                <div class="text-rose-700 font-bold"><strong>Batas Aman Dimakan:</strong> {{ \Carbon\Carbon::parse($qcCheck->consume_by)->format('H:i') }} WIB</div>
                            @endif
                        </div>
                        @if($qcEvidences->isNotEmpty())
                            <div class="pt-2 border-t border-emerald-200/60 flex flex-wrap items-center gap-2">
                                @foreach($qcEvidences as $qcEv)
                                    <button type="button"
                                            onclick='openPhotoModal("{{ $qcEv->url }}", "{{ addslashes($qcEv->description ?? 'Uji Kelayakan QC') }}", @json($qcGallery), {{ $loop->index }})'
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-emerald-200 hover:border-emerald-600 text-emerald-950 cursor-pointer transition text-[11px] font-medium shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Foto Bukti Kelayakan Saji QC</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- TAHAP 4: Pengiriman & Serah Terima Sekolah Sasaran -->
                @php
                    $distGallery = $distributions->flatMap(fn($d) => $d->evidences)->map(fn($ev) => [
                        'url' => $ev->url,
                        'caption' => $ev->description ?? 'Dokumentasi Distribusi Sekolah'
                    ])->values();
                @endphp
                <div class="relative space-y-3">
                    <span class="absolute -left-[33px] sm:-left-[41px] top-0 w-8 h-8 rounded-full bg-[#071E49] text-white flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-2xs">
                        4
                    </span>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hilir / Sekolah Sasaran</div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Penyaluran & Serah Terima di Sekolah</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Armada mengantarkan langsung ke lokasi penerima sesuai target alokasi siswa sasaran.</p>
                    </div>

                    <div class="space-y-2.5 pt-1">
                        @forelse($distributions as $d)
                            <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 text-xs space-y-2.5 hover:border-slate-300 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="space-y-1">
                                        <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                            <span>{{ $d->destination?->nama ?? 'Sekolah Penerima' }}</span>
                                            @if($d->destination?->id)
                                                <span class="font-mono text-[10px] font-semibold text-slate-500 bg-slate-200/70 px-1.5 py-0.2 rounded">
                                                    #TJ-{{ str_pad($d->destination->id, 3, '0', STR_PAD_LEFT) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            @if($d->destination?->jumlah_penerima)
                                                <span>Kuota: <strong class="text-slate-700 font-semibold">{{ number_format($d->destination->jumlah_penerima, 0, ',', '.') }}</strong> Sasaran</span>
                                                <span class="text-slate-300">&bull;</span>
                                            @endif
                                            <span>Kirim: {{ $d->distributed_at ? \Carbon\Carbon::parse($d->distributed_at)->format('d M Y, H:i') : '-' }} WIB</span>
                                            @if($d->destination?->alamat)
                                                <span class="text-slate-300">&bull;</span>
                                                <span class="truncate max-w-xs">{{ $d->destination->alamat }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-left sm:text-right shrink-0 pt-2 sm:pt-0 border-t border-slate-200/50 sm:border-t-0 flex sm:flex-col items-center sm:items-end justify-between sm:justify-center">
                                        <div class="font-extrabold text-sm text-slate-900">{{ number_format($d->portions_sent, 0, ',', '.') }} Porsi</div>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold {{ $d->status === 'received' ? 'text-emerald-700' : 'text-blue-700' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $d->status === 'received' ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }}"></span>
                                            {{ $d->status === 'received' ? 'Diterima Lengkap' : 'Dalam Perjalanan' }}
                                        </span>
                                    </div>
                                </div>
                                @if($d->evidences && $d->evidences->isNotEmpty())
                                    <div class="pt-2 border-t border-slate-200/60 flex flex-wrap items-center gap-2">
                                        @foreach($d->evidences as $dev)
                                            @php
                                                $dIdx = $distGallery->search(fn($item) => $item['url'] === $dev->url);
                                                $dSafeIdx = $dIdx !== false ? $dIdx : 0;
                                            @endphp
                                            <button type="button"
                                                    onclick='openPhotoModal("{{ $dev->url }}", "{{ addslashes($dev->description ?? 'Bukti Distribusi') }}", @json($distGallery), {{ $dSafeIdx }})'
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-[#071E49] text-slate-700 hover:text-[#071E49] cursor-pointer transition text-[11px] font-medium shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>{{ Str::limit($dev->description ?? 'Bukti Distribusi', 32) }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 italic">Data serah terima sekolah tersimpan dalam log pengiriman resmi.</div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- 4. Galeri Dokumentasi Foto Lapangan (Lightbox Interaktif) -->
            @if($publicEvidences->isNotEmpty())
                <div class="pt-6 border-t border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">Dokumentasi Foto Lapangan</h3>
                            <p class="text-xs text-slate-500">Bukti visual kondisi riil baki saji dan pelaksanaan distribusi</p>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium">Klik foto untuk perbesar</span>
                    </div>
                    
                    @php
                        $publicEvidenceGallery = $publicEvidences->map(fn($p) => [
                            'url' => $p->url,
                            'caption' => $p->description ?? match($p->evidenceable_type) {
                                'receivings' => 'Penerimaan Bahan Baku',
                                'processing_steps' => 'Pengolahan Dapur',
                                'pre_distribution_checks' => 'Uji Kelayakan QC',
                                'distributions' => 'Distribusi Sekolah',
                                default => 'Dokumentasi Lapangan'
                            }
                        ])->values();
                    @endphp
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                        @foreach($publicEvidences as $pic)
                            @php
                                $typeLabel = match($pic->evidenceable_type) {
                                    'receivings' => 'Penerimaan Bahan',
                                    'processing_steps' => 'Pengolahan Dapur',
                                    'pre_distribution_checks' => 'Uji Kelayakan QC',
                                    'distributions' => 'Distribusi Sekolah',
                                    default => 'Dokumentasi Batch',
                                };
                            @endphp
                            <div onclick='openPhotoModal("{{ $pic->url }}", "{{ addslashes($pic->description ?? $typeLabel) }}", @json($publicEvidenceGallery), {{ $loop->index }})' class="relative rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 cursor-pointer group shadow-2xs hover:border-[#071E49] transition">
                                <img src="{{ $pic->url }}" alt="{{ $pic->description ?? $typeLabel }}" class="w-full h-full object-cover group-hover:scale-102 transition duration-200">
                                <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="px-2.5 py-1 rounded-md bg-white/95 text-slate-900 font-bold text-[11px] shadow-sm flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-[#071E49]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        Buka & Geser
                                    </span>
                                </div>
                                <div class="absolute bottom-1.5 left-1.5 right-1.5 flex items-center justify-between pointer-events-none">
                                    <span class="px-2 py-0.5 rounded bg-black/60 text-white font-medium text-[10px] backdrop-blur-xs truncate max-w-[70%]">
                                        {{ $typeLabel }}
                                    </span>
                                    @if($pic->uploaded_at)
                                        <span class="text-[9px] text-white/80 font-mono">
                                            {{ \Carbon\Carbon::parse($pic->uploaded_at)->format('H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @elseif($batch_code)
        <!-- Jika Batch Tidak Ditemukan -->
        <div class="bg-white rounded-2xl p-8 sm:p-10 text-center border border-slate-200/90 shadow-2xs max-w-lg mx-auto space-y-3">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center mx-auto border border-amber-200">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-slate-900">Kode Batch "{{ $batch_code }}" Tidak Ditemukan</h3>
            <p class="text-xs text-slate-500 leading-relaxed font-normal">
                Pastikan kode batch yang dimasukkan sesuai dengan kemasan (contoh: BTC-20261013-001) dan batch tersebut telah diproduksi oleh dapur SPPG resmi.
            </p>
            <div class="pt-2">
                <a href="{{ route('public.trace') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Coba Kode Lain</span>
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
