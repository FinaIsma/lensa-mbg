@extends('layouts.admin')

@section('title', 'Correction Request')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Header & Back Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Correction Request</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Correction Request
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Tinjau dan proses permohonan koreksi data operasional yang diajukan oleh unit SPPG.
            </p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition-colors self-start sm:self-auto">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Total Pengajuan</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $counts['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Menunggu Verifikasi</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ $counts['menunggu'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Disetujui</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $counts['disetujui'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Ditolak</p>
            <p class="text-2xl font-bold text-rose-500 mt-1">{{ $counts['ditolak'] }}</p>
        </div>
    </div>

    <!-- Filter and Search Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/70 shadow-sm">
        <form method="GET" action="{{ route('admin.correction-requests.index') }}" class="flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="relative w-full sm:w-80">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari ID, SPPG, pemohon..." 
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-xs text-slate-700 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="all" {{ $status === 'all' || !$status ? 'selected' : '' }}>Semua Status</option>
                    <option value="Menunggu" {{ $status === 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="Disetujui" {{ $status === 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Ditolak" {{ $status === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <select name="jenis_data" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-xs text-slate-700 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="all" {{ $jenisData === 'all' || !$jenisData ? 'selected' : '' }}>Semua Jenis Data</option>
                    <option value="Distribusi" {{ $jenisData === 'Distribusi' ? 'selected' : '' }}>Distribusi</option>
                    <option value="Produksi" {{ $jenisData === 'Produksi' ? 'selected' : '' }}>Produksi</option>
                    <option value="Penerimaan" {{ $jenisData === 'Penerimaan' ? 'selected' : '' }}>Penerimaan</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-5">ID & Tanggal</th>
                        <th class="py-3.5 px-5">SPPG Pemohon</th>
                        <th class="py-3.5 px-5">Jenis Data</th>
                        <th class="py-3.5 px-5">Ringkasan & Alasan</th>
                        <th class="py-3.5 px-5 text-center">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($filteredRequests as $req)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-5">
                            <span class="font-bold text-slate-900">{{ $req['id'] }}</span>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $req['tanggal'] }}</div>
                        </td>
                        <td class="py-3.5 px-5">
                            <div class="font-semibold text-slate-800">{{ $req['sppg_nama'] }}</div>
                            <div class="text-[11px] text-slate-500">Oleh: {{ $req['pemohon'] }}</div>
                        </td>
                        <td class="py-3.5 px-5">
                            <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $req['jenis_data'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 max-w-xs">
                            <div class="font-medium text-slate-800 truncate">{{ $req['ringkasan'] }}</div>
                            <div class="text-[11px] text-slate-400 truncate mt-0.5">Alasan: {{ $req['alasan'] }}</div>
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            @if($req['status'] === 'Menunggu')
                                <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-semibold bg-[#FEF3C7] text-[#D97706]">
                                    Menunggu
                                </span>
                            @elseif($req['status'] === 'Disetujui')
                                <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-semibold bg-[#DCFCE7] text-[#16A34A]">
                                    Disetujui
                                </span>
                            @else
                                <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-semibold bg-[#FEE2E2] text-[#DC2626]">
                                    Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-right space-x-1">
                            <button type="button" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium transition-colors">
                                Review
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            Tidak ada pengajuan koreksi yang sesuai kriteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
