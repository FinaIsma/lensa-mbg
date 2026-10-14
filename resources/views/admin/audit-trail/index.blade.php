@extends('layouts.admin')

@section('title', 'Audit Trail')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Header & Back Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Audit Trail</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Audit Trail
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Rekam jejak seluruh riwayat aktivitas, perubahan data, dan akses sistem Lensa MBG secara real-time.
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
            <p class="text-xs text-slate-500 font-medium">Total Log Aktivitas</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $counts['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Master Data</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $counts['master_data'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Terkait Koreksi</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ $counts['koreksi'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200/70 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Total Pengguna Terlibat</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $counts['user'] }}</p>
        </div>
    </div>

    <!-- Filter and Search Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/70 shadow-sm">
        <form method="GET" action="{{ route('admin.audit-trail.index') }}" class="flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="relative w-full sm:w-80">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari action, target, user..." 
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="modul" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-xs text-slate-700 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="all" {{ $modul === 'all' || !$modul ? 'selected' : '' }}>Semua Modul</option>
                    <option value="Penerimaan" {{ $modul === 'Penerimaan' ? 'selected' : '' }}>Penerimaan</option>
                    <option value="Produksi" {{ $modul === 'Produksi' ? 'selected' : '' }}>Produksi</option>
                    <option value="Distribusi" {{ $modul === 'Distribusi' ? 'selected' : '' }}>Distribusi</option>
                    <option value="Master Data" {{ $modul === 'Master Data' ? 'selected' : '' }}>Master Data</option>
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
                        <th class="py-3.5 px-5">ID & Waktu</th>
                        <th class="py-3.5 px-5">Pengguna</th>
                        <th class="py-3.5 px-5">Action</th>
                        <th class="py-3.5 px-5">Target (Auditable)</th>
                        <th class="py-3.5 px-5">Perubahan (Old → New)</th>
                        <th class="py-3.5 px-5 text-right">CR Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($filteredLogs as $log)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-5">
                            <span class="font-bold text-slate-900">#{{ $log['id'] }}</span>
                            <div class="text-[11px] text-slate-400 mt-0.5 whitespace-nowrap">{{ $log['waktu'] }}</div>
                        </td>
                        <td class="py-3.5 px-5">
                            <div class="font-semibold text-slate-800">{{ $log['pengguna'] }}</div>
                            <div class="text-[10px] text-slate-400">{{ $log['role'] }}</div>
                        </td>
                        <td class="py-3.5 px-5">
                            @if($log['action'] === 'confirm')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                    confirm
                                </span>
                            @elseif(str_contains($log['action'], 'approve'))
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/50">
                                    {{ $log['action'] }}
                                </span>
                            @elseif(str_contains($log['action'], 'reject'))
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/50">
                                    {{ $log['action'] }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $log['action'] }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5">
                            <div class="font-semibold text-slate-800">{{ $log['auditable_type'] }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">ID: #{{ $log['auditable_id'] }}</div>
                        </td>
                        <td class="py-3.5 px-5 max-w-md">
                            @if($log['old_values'] || $log['new_values'])
                                <div class="font-mono text-[11px] bg-slate-50 p-2 rounded-lg border border-slate-100 space-y-1">
                                    @if($log['old_values'])
                                        <div class="text-rose-600 truncate"><span class="font-semibold text-slate-500">Old:</span> {{ $log['old_values'] }}</div>
                                    @endif
                                    @if($log['new_values'])
                                        <div class="text-emerald-600 truncate"><span class="font-semibold text-slate-500">New:</span> {{ $log['new_values'] }}</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[11px]">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-right font-mono text-xs">
                            @if($log['correction_request_id'])
                                <span class="inline-block px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded font-semibold text-[10px]">
                                    CR #{{ $log['correction_request_id'] }}
                                </span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            Tidak ada riwayat aktivitas yang sesuai kriteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
