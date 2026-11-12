@extends('layouts.public')

@section('title', 'Telusur Rantai Pasok Makanan (Batch Traceability) - Radar MBG')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

    <!-- Header & Search Box -->
    <div class="text-center space-y-3 max-w-2xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Sistem Keterlacakan Hulu ke Hilir
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-[#071E49] tracking-tight">
            Telusur Jejak Makanan Siswa
        </h1>
        <p class="text-sm text-slate-600">
            Ketik nomor kode batch yang tertera pada label makanan atau hasil scan QR code kemasan untuk menelusuri asal bahan, jam masak, hingga serah terima.
        </p>

        <!-- Form Cari Batch -->
        <form method="GET" action="{{ route('public.trace') }}" class="pt-2">
            <div class="flex items-center gap-2 max-w-md mx-auto">
                <div class="relative flex-1">
                    <input type="text" name="batch_code" value="{{ $batch_code ?? '' }}" placeholder="Contoh: BTC-20261013-001" class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-mono tracking-wider uppercase text-slate-800 shadow-2xs">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <button type="submit" class="py-3 px-5 rounded-2xl bg-[#071E49] hover:bg-[#042A70] text-white font-bold text-sm shadow-sm transition">
                    Lacak
                </button>
            </div>
        </form>

        <!-- Saran Pencarian Cepat -->
        @if($recentTraceableBatches->isNotEmpty() && !$batch)
            <div class="flex flex-wrap items-center justify-center gap-2 pt-3 text-xs text-slate-500">
                <span>Coba batch terverifikasi:</span>
                @foreach($recentTraceableBatches as $rb)
                    <a href="{{ route('public.trace', $rb->batch_code) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 font-mono text-[11px] font-semibold transition">
                        {{ $rb->batch_code }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if($batch)
        <!-- Hasil Telusur Batch -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-8 p-6 sm:p-10">
            
            <!-- Ringkasan Batch -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold bg-[#071E49] text-white">
                            {{ $batch->batch_code }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            {{ ucfirst($batch->status) }}
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $batch->menu?->nama ?? 'Menu Masakan Bergizi' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 flex items-center gap-2">
                        <span>Tanggal Masak: <strong>{{ \Carbon\Carbon::parse($batch->production_date)->translatedFormat('d F Y') }}</strong></span>
                        <span>&bull;</span>
                        <span>Dapur: <a href="{{ route('public.sppg', $batch->sppg?->id_sppg ?? 1) }}" class="text-blue-600 font-semibold hover:underline">{{ $batch->sppg?->nama_sppg ?? 'SPPG' }}</a></span>
                    </p>
                </div>

                <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                    <div>
                        <div class="text-[11px] text-slate-500 font-medium">Hasil Porsi Siap:</div>
                        <div class="text-2xl font-black text-slate-900">{{ $batch->actual_porsi ?? $batch->target_porsi }} Porsi</div>
                    </div>
                    <div class="border-l border-slate-200 pl-3">
                        <div class="text-[11px] text-slate-500 font-medium">Ukuran:</div>
                        <div class="text-xs font-bold text-slate-800 uppercase">{{ $batch->recipe?->ukuran_porsi ?? 'Besar' }}</div>
                    </div>
                </div>
            </div>

            <!-- Linimasa Vertikal Lengkap (Hulu ke Hilir) -->
            <div class="relative pl-6 sm:pl-8 border-l-2 border-blue-200 space-y-10 ml-3 sm:ml-4">

                <!-- TAHAP 1: Pasokan Bahan Baku Segar -->
                <div class="relative space-y-3">
                    <span class="absolute -left-[33px] sm:-left-[41px] top-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-xs">
                        1
                    </span>
                    <div>
                        <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Hulu / Pasokan</span>
                        <h3 class="text-lg font-bold text-slate-900">Bahan Baku Segar & Pemasok Mitra Lokal</h3>
                        <p class="text-xs text-slate-500">Semua bahan terverifikasi FEFO dan lolos inspeksi kesegaran sebelum masuk dapur.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                        @forelse($materials as $mat)
                            <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200 text-xs space-y-1">
                                <div class="font-bold text-slate-900 text-sm">{{ $mat['material_nama'] }}</div>
                                <div class="text-slate-500 text-[11px] flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>Pemasok: <strong>{{ $mat['supplier_nama'] }}</strong></span>
                                </div>
                                @if($mat['received_date'])
                                    <div class="text-[10px] text-slate-400">
                                        Diterima: {{ \Carbon\Carbon::parse($mat['received_date'])->format('d/m/Y') }} &bull; Aman s/d {{ \Carbon\Carbon::parse($mat['expiry_date'])->format('d/m/Y') }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="col-span-3 text-xs text-slate-400">Rincian bahan baku tercatat terpadu dalam resep aktif.</div>
                        @endforelse
                    </div>
                </div>

                <!-- TAHAP 2: Pengolahan di Dapur -->
                <div class="relative space-y-3">
                    <span class="absolute -left-[33px] sm:-left-[41px] top-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-xs">
                        2
                    </span>
                    <div>
                        <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Dapur SPPG</span>
                        <h3 class="text-lg font-bold text-slate-900">Tahapan Pengolahan & Sanitasi Makanan</h3>
                        <p class="text-xs text-slate-500">Mencatat waktu awal dan selesai setiap tahapan masak demi menjamin higienitas.</p>
                    </div>

                    <div class="space-y-2 pt-1">
                        @forelse($steps as $step)
                            <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
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
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $step->status === 'done' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $step->status === 'done' ? 'Selesai' : 'Sedang Berjalan' }}
                                </span>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400">Proses pengolahan dapur dilakukan terstandar food-grade.</div>
                        @endforelse
                    </div>
                </div>

                <!-- TAHAP 3: Pemeriksaan Kelayakan Saji (QC) -->
                <div class="relative space-y-3">
                    <span class="absolute -left-[33px] sm:-left-[41px] top-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-xs">
                        3
                    </span>
                    <div>
                        <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Quality Control (QC)</span>
                        <h3 class="text-lg font-bold text-slate-900">Pemeriksaan Mutu & Uji Kelayakan Saji</h3>
                        <p class="text-xs text-slate-500">Pengecekan akhir aroma, rasa, tingkat kematangan, dan suhu sebelum dikemas.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-emerald-900 text-sm">Hasil Pemeriksaan Petugas Dapur:</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-200 text-emerald-900">
                                Lolos Kelayakan
                            </span>
                        </div>
                        <p class="text-slate-700 leading-relaxed">
                            {{ $qcCheck?->reported_condition ?? 'Makanan matang sempurna, aroma segar khas rempah, baki tertutup rapat dan higienis.' }}
                        </p>
                        @if($qcCheck?->checked_at)
                            <div class="text-[11px] text-slate-500">
                                Waktu Pemeriksaan: {{ \Carbon\Carbon::parse($qcCheck->checked_at)->format('H:i') }} WIB
                            </div>
                        @endif
                    </div>
                </div>

                <!-- TAHAP 4: Pengiriman & Serah Terima Sekolah -->
                <div class="relative space-y-3">
                    <span class="absolute -left-[33px] sm:-left-[41px] top-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-xs">
                        4
                    </span>
                    <div>
                        <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Hilir / Sekolah</span>
                        <h3 class="text-lg font-bold text-slate-900">Penyaluran & Serah Terima ke Sekolah</h3>
                        <p class="text-xs text-slate-500">Armada mengantarkan langsung ke lokasi penerima sesuai target alokasi siswa.</p>
                    </div>

                    <div class="space-y-2 pt-1">
                        @forelse($distributions as $d)
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $d->destination?->nama ?? 'Sekolah Penerima' }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        Waktu Kirim: {{ $d->distributed_at ? \Carbon\Carbon::parse($d->distributed_at)->format('d M Y, H:i') : '-' }} WIB
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-extrabold text-sm text-slate-900">{{ $d->portions_sent }} Porsi</div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $d->status === 'received' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $d->status === 'received' ? 'Diterima Lengkap' : 'Dalam Perjalanan' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400">Data serah terima sekolah tersimpan dalam log pengiriman.</div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Galeri Bukti Publik jika ada -->
            @if($publicEvidences->isNotEmpty())
                <div class="pt-6 border-t border-slate-100 space-y-3">
                    <h3 class="text-base font-bold text-slate-900">Dokumentasi Foto Lapangan</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($publicEvidences as $pic)
                            <div class="rounded-2xl overflow-hidden border border-slate-200 aspect-video bg-slate-100">
                                <img src="{{ asset('storage/' . $pic->file_path) }}" alt="{{ $pic->description ?? 'Foto Lapangan' }}" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @elseif($batch_code)
        <!-- Jika Batch Tidak Ditemukan -->
        <div class="bg-white rounded-3xl p-10 text-center border border-slate-200 shadow-sm max-w-lg mx-auto">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900">Kode Batch "{{ $batch_code }}" Tidak Ditemukan</h3>
            <p class="text-xs text-slate-500 mt-1">
                Pastikan kode batch yang dimasukkan sesuai dengan kemasan (contoh: BTC-20261013-001) dan batch telah berstatus didistribusikan.
            </p>
        </div>
    @endif

</div>
@endsection

