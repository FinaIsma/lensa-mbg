<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SppgManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin_sistem') {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::user()->role === 'admin_sppg') {
            return redirect()->route('sppg.dashboard');
        }
    }

    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.process');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.process');

// Local API routes for Wilayah Indonesia
Route::get('/api/wilayah/provinces', [WilayahController::class, 'getProvinces']);
Route::get('/api/wilayah/regencies/{provinceId}', [WilayahController::class, 'getRegencies']);
Route::get('/api/wilayah/districts/{regencyId}', [WilayahController::class, 'getDistricts']);

// Admin Sistem Routes
Route::middleware(['auth', 'role:admin_sistem'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sppg', [SppgManagementController::class, 'index'])->name('sppg.index');
    Route::get('/sppg/{id}', [SppgManagementController::class, 'show'])->name('sppg.show');
    Route::post('/sppg/{id}/status', [SppgManagementController::class, 'updateStatus'])->name('sppg.updateStatus');

    // Interactive placeholder routes for sidebar items
    Route::get('/monitoring', function () {
        return redirect()->route('admin.dashboard')->with('info', 'Fitur Monitoring sedang dalam tahap pengembangan.');
    })->name('monitoring');

    Route::get('/correction-request', function () {
        return redirect()->route('admin.dashboard')->with('info', 'Fitur Correction Request sedang dalam tahap pengembangan.');
    })->name('correction');

    Route::get('/audit-trail', function () {
        return redirect()->route('admin.dashboard')->with('info', 'Fitur Audit Trail sedang dalam tahap pengembangan.');
    })->name('audit');
});
