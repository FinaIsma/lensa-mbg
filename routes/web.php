<?php

use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\CorrectionRequestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'admin_sistem') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('sppg.dashboard');
    }

    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.process');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

<<<<<<< HEAD
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/correction-requests', [CorrectionRequestController::class, 'index'])->name('correction-requests.index');
    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');
});
=======
Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.process');

// Local API routes for Wilayah Indonesia
Route::get('/api/wilayah/provinces', [WilayahController::class, 'getProvinces']);
Route::get('/api/wilayah/regencies/{provinceId}', [WilayahController::class, 'getRegencies']);
Route::get('/api/wilayah/districts/{regencyId}', [WilayahController::class, 'getDistricts']);

use App\Http\Controllers\Admin\CorrectionRequestController;

Route::middleware(['auth', 'role:admin_sistem'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sppg', [SppgManagementController::class, 'index'])->name('sppg.index');
    Route::get('/sppg/{id}', [SppgManagementController::class, 'show'])->name('sppg.show');
    Route::post('/sppg/{id}/status', [SppgManagementController::class, 'updateStatus'])->name('sppg.updateStatus');
    // Interactive placeholder routes for sidebar items
    Route::get('/monitoring', function () {
        return redirect()->route('admin.dashboard')->with('info', 'Fitur Monitoring sedang dalam tahap pengembangan.');
    })->name('monitoring');

    Route::get('/correction-request', [CorrectionRequestController::class, 'index'])->name('correction');
    Route::post('/correction-request/{id}/approve', [CorrectionRequestController::class, 'approve'])->name('correction.approve');
    Route::post('/correction-request/{id}/reject', [CorrectionRequestController::class, 'reject'])->name('correction.reject');

    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit');
});
// API route to check email uniqueness dynamically
Route::get('/api/check-email', [RegisterController::class, 'checkEmail'])->name('api.check-email');
>>>>>>> origin/Main
