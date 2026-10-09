<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WilayahController;

Route::get('/', function () {
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

// API route to check email uniqueness dynamically
Route::get('/api/check-email', [RegisterController::class, 'checkEmail'])->name('api.check-email');