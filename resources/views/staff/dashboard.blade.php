@extends('layouts.staff')

@section('title', 'Dashboard Operasional - Staff SPPG')

@section('content')
<div class="space-y-7 pb-8">
    {{-- Page Title & Subtitle --}}
    <div class="page-title-section">
        <h1 class="page-title">
            Selamat datang, {{ $user->nama ?? 'Staff' }} di Dashboard Operasional SPPG
        </h1>
        <p class="page-subtitle">
            Ringkasan data dan aktivitas operasional SPPG secara real-time.
        </p>
    </div>

    {{-- 4 Stat Cards Grid --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Total Menu --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="9" y1="9" x2="15" y2="9"></line>
                    <line x1="9" y1="13" x2="15" y2="13"></line>
                    <line x1="9" y1="17" x2="11" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Menu</span>
                <span class="stat-value">{{ number_format($stats['total_menu'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Menu Terdaftar</span>
            </div>
        </div>

        {{-- Card 2: Total Resep --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Resep</span>
                <span class="stat-value">{{ number_format($stats['total_resep'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Resep Terdaftar</span>
            </div>
        </div>

        {{-- Card 3: Menu Aktif --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Menu Aktif</span>
                <span class="stat-value">{{ number_format($stats['menu_aktif'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Siap Digunakan</span>
            </div>
        </div>

        {{-- Card 4: Resep Aktif --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Resep Aktif</span>
                <span class="stat-value">{{ number_format($stats['resep_aktif'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Formula Terverifikasi</span>
            </div>
        </div>
    </div>

    {{-- Main Dashboard Card (Empty State Bergaya Kepala SPPG & Admin) --}}
    <div class="table-card" style="padding: 56px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 380px;">
        <div style="width: 80px; height: 80px; border-radius: 50%; background-color: #F1F5F9; display: flex; align-items: center; justify-content: center; margin-bottom: 22px; color: #071E49;">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1"></rect>
            </svg>
        </div>
        <h2 style="font-size: 20px; font-weight: 700; color: #071E49; margin-bottom: 8px;">
            Dashboard Operasional Staff SPPG
        </h2>
        <p style="font-size: 14px; color: #64748B; max-width: 520px; line-height: 1.6; margin: 0 auto 24px;">
            Aktivitas harian dari pengadaan bahan, pengelolaan stok, jadwal produksi batch, dan distribusi makanan bergizi akan terangkum di sini secara otomatis.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('staff.menu-resep.index') }}" class="btn-tambah-staff" style="text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
                <span>Buka Master Menu & Resep</span>
            </a>
        </div>
    </div>
</div>
@endsection
