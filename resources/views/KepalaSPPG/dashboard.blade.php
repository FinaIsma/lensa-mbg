@extends('layouts.kepala-sppg')

@section('title', 'Dashboard & Analytics')

@push('styles')
    @vite('resources/css/KepalaSPPG/dashboard.css')
@endpush

@section('content')
<div class="space-y-8 pb-12">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[#071E49] dashboard-heading tracking-tight">
            Dashboard & Analytics
        </h1>
        <p class="text-sm text-slate-500 mt-1 font-normal">
            Ringkasan data dan aktivitas sistem Lensa MBG secara real-time.
        </p>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/KepalaSPPG/dashboard.js')
@endpush
