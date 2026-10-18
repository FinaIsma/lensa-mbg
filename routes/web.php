<?php

use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\CorrectionRequestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SppgManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Sppg\DashboardController as SppgDashboardController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth('staff')->user() ?: auth()->user();

    if ($user) {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'kepala_sppg') {
            if (Route::has('sppg.dashboard')) {
                return redirect()->route('sppg.dashboard');
            }
        }

        if ($user->role === 'staff') {
            if (Route::has('staff.dashboard')) {
                return redirect()->route('staff.dashboard');
            }
        }

        return redirect()->route('login');
    }

    return redirect()->route('login');
});

Route::middleware('guest:web,staff')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.process');

    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.process');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Local API routes for Wilayah Indonesia
Route::get('/api/wilayah/provinces', [WilayahController::class, 'getProvinces']);
Route::get('/api/wilayah/regencies/{provinceId}', [WilayahController::class, 'getRegencies']);
Route::get('/api/wilayah/districts/{regencyId}', [WilayahController::class, 'getDistricts']);
Route::get('/api/check-email', [RegisterController::class, 'checkEmail'])->name('api.check-email');

Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/users', '/admin/sppg')->name('users.index');
    Route::get('/sppg', [SppgManagementController::class, 'index'])->name('sppg.index');
    Route::get('/sppg/{id}', [SppgManagementController::class, 'show'])->name('sppg.show');
    Route::post('/sppg/{id}/status', [SppgManagementController::class, 'updateStatus'])->name('sppg.updateStatus');

    Route::get('/monitoring', function () {
        return redirect()->route('admin.dashboard')->with('info', 'Fitur Monitoring sedang dalam tahap pengembangan.');
    })->name('monitoring');

    Route::get('/correction-requests', [CorrectionRequestController::class, 'index'])->name('correction-requests.index');
    Route::get('/correction-request', [CorrectionRequestController::class, 'index'])->name('correction');
    Route::post('/correction-request/{id}/approve', [CorrectionRequestController::class, 'approve'])->name('correction.approve');
    Route::post('/correction-request/{id}/reject', [CorrectionRequestController::class, 'reject'])->name('correction.reject');

    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit');
    Route::get('/audit', [AuditTrailController::class, 'index'])->name('audit-trail.index');
});

Route::middleware(['role:kepala_sppg'])->prefix('sppg')->name('sppg.')->group(function () {
    Route::get('/dashboard', [SppgDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
});
