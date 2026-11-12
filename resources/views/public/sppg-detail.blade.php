@extends('layouts.public')

@section('title', $sppg->nama_sppg . ' - Profil & Transparansi Dapur MBG')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

    <!-- Tombol Kembali -->
    <div>
        <a href="{{ route('public.home') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-blue-600 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Radar MBG</span>
        </a>
    </div>

    <!-- Header Profil Dapur SPPG -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Kode Unit: SPPG{{ str_pad($sppg->id_sppg, 3, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Terverifikasi BGN
                    </span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-[#071E49] tracking-tight">
                    {{ $sppg->nama_sppg }}
                </h1>
                <p class="text-sm text-slate-600 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ $sppg->alamat_sppg ?? 'Alamat dapur operasional' }} &bull; {{ $sppg->kecamatan ? $sppg->kecamatan . ', ' : '' }}{{ $sppg->kabupaten_kota ?? '' }}</span>
                </p>
                @if($sppg->no_telepon)
                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>Kontak Unit Layanan: {{ $sppg->no_telepon }}</span>
                    </p>
                @endif
            </div>

            <!-- Metrik 7 Hari -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                    <div class="text-xl sm:text-2xl font-black text-[#071E49]">
                        {{ number_format($porsiDistribusi7Hari, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">Porsi Terkirim (7 Hari)</div>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                    <div class="text-xl sm:text-2xl font-black text-emerald-600">
                        {{ $sekolahBinaan->count() }}
                    </div>
                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">Sekolah Mitra Binaan</div>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100 col-span-2 sm:col-span-1">
                    <div class="text-xl sm:text-2xl font-black text-blue-600">
                        100%
                    </div>
                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">Disiplin Pencatatan</div>
                </div>
            </div>
        </div>

        <!-- Menu Aktif & Informasi Gizi -->
        <div class="pt-8 space-y-6">
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Daftar Menu & Kepatuhan Gizi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Komposisi nutrisi telah dikonfigurasi berdasarkan takaran porsi kebutuhan gizi anak sekolah.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @forelse($activeMenus as $m)
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">
                                    {{ $m->kategori_label }}
                                </span>
                                <h4 class="font-bold text-slate-900 text-base mt-1.5">{{ $m->nama }}</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $m->deskripsi ?: 'Menu bergizi terpadu dengan bahan segar pilihan.' }}</p>
                            </div>
                        </div>

                        <!-- Nilai Gizi jika ada resep aktif -->
                        @php
                            $activeRecipe = $m->recipes->first();
                        @endphp
                        @if($activeRecipe && $activeRecipe->nutritions->isNotEmpty())
                            <div class="pt-3 border-t border-slate-100">
                                <div class="text-[11px] font-semibold text-slate-600 mb-2">Nilai Gizi per Porsi (Ukuran {{ ucfirst($activeRecipe->ukuran_porsi) }}):</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($activeRecipe->nutritions as $nut)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700">
                                            <span class="font-medium text-slate-500">{{ $nut->nutritionType?->nama ?? 'Gizi' }}:</span>
                                            <strong class="text-slate-900">{{ (float)$nut->nilai }} {{ $nut->nutritionType?->satuan ?? '' }}</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-2 py-8 text-center text-slate-400 text-sm">
                        Belum ada menu yang dipublikasikan oleh unit dapur ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sekolah / Sasaran yang Dilayani -->
        <div class="pt-8 space-y-4 border-t border-slate-100 mt-8">
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Sekolah & Sasaran Terdaftar</h3>
                <p class="text-xs text-slate-500">Daftar lembaga penerima manfaat di bawah naungan unit dapur SPPG ini.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($sekolahBinaan as $target)
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <h5 class="font-bold text-slate-900 text-sm">{{ $target->nama }}</h5>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $target->alamat ?? '-' }}</p>
                        </div>
                        <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-1 rounded-lg">
                            {{ $target->jumlah_penerima }} Siswa
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Riwayat Distribusi Terbaru -->
        <div class="pt-8 space-y-4 border-t border-slate-100 mt-8">
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">Penyaluran Makanan Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                            <th class="py-3 px-3">Tanggal & Jam</th>
                            <th class="py-3 px-3">Sekolah Tujuan</th>
                            <th class="py-3 px-3">Menu</th>
                            <th class="py-3 px-3 text-right">Porsi</th>
                            <th class="py-3 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentDistributions as $dist)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3 text-xs text-slate-600 font-mono">
                                    {{ $dist->distributed_at ? \Carbon\Carbon::parse($dist->distributed_at)->format('d M Y, H:i') : '-' }} WIB
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-900 text-xs">
                                    {{ $dist->destination?->nama ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-xs text-slate-600">
                                    {{ $dist->batch?->menu?->nama ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-xs font-bold text-right text-slate-900">
                                    {{ number_format($dist->portions_sent, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($dist->status === 'received')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            Diterima
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                            Terkirim
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 text-xs">
                                    Belum ada log distribusi tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

