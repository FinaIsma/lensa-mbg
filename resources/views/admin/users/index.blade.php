@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Header & Back Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">User Management</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                User Management
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola akun pengguna SPPG, verifikasi identitas, dan atur status akses operasional.
            </p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition-colors self-start sm:self-auto">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Quick Stats Summary -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Total Pengguna</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_user'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">SPPG Terdaftar</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['total_sppg'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Status Aktif</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['aktif'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Menunggu Verifikasi</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ $stats['menunggu'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filter and Search Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/70 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="relative w-full sm:w-80">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, SPPG, email..." 
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-xs text-slate-700 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 w-full sm:w-auto">
                    <option value="all" {{ $status === 'all' || !$status ? 'selected' : '' }}>Semua Status</option>
                    <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="menunggu_verifikasi" {{ $status === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
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
                        <th class="py-3.5 px-5">Pengguna & Email</th>
                        <th class="py-3.5 px-5">Nama SPPG</th>
                        <th class="py-3.5 px-5">Wilayah</th>
                        <th class="py-3.5 px-5">Kontak</th>
                        <th class="py-3.5 px-5 text-center">Status Verifikasi</th>
                        <th class="py-3.5 px-5 text-center">Status Akun</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($sppgUsers as $item)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-5">
                            <div class="font-bold text-slate-900">{{ $item->user->nama ?? $item->nama_pegawai ?? 'Pengguna SPPG' }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $item->user->email ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-5 font-semibold text-slate-800">
                            {{ $item->nama_sppg }}
                        </td>
                        <td class="py-3.5 px-5 text-slate-600">
                            <div>{{ $item->kabupaten_kota ?? '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $item->provinsi ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-5 text-slate-600">
                            {{ $item->no_telepon ?? '-' }}
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            @if($item->status === 'menunggu_verifikasi')
                                <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-semibold bg-[#FEF3C7] text-[#B45309]">
                                    Belum Diverifikasi
                                </span>
                            @else
                                <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-semibold bg-[#CCFBF1] text-[#0F766E]">
                                    Terverifikasi
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            @if($item->status === 'aktif')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-semibold bg-[#DCFCE7] text-[#15803D]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#15803D]"></span>
                                    Aktif
                                </span>
                            @elseif($item->status === 'nonaktif')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-semibold bg-[#FEE2E2] text-[#DC2626]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626]"></span>
                                    Nonaktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-semibold bg-[#FEF3C7] text-[#B45309]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#B45309]"></span>
                                    Menunggu
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-right">
                            <button type="button" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition-colors">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Tidak ada data pengguna SPPG yang sesuai kriteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sppgUsers->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $sppgUsers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
