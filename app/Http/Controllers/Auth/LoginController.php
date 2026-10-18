<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class LoginController extends Controller
{
    public function showLogin()
    {
        $user = auth('staff')->user() ?: auth()->user();

        if ($user) {
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if ($user->role === 'kepala_sppg' && Route::has('sppg.dashboard')) {
                return redirect()->route('sppg.dashboard');
            }

            if ($user->role === 'staff' && Route::has('staff.dashboard')) {
                return redirect()->route('staff.dashboard');
            }

            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required', 'in:admin,sppg,kepala_sppg,staff'],
        ]);

        // 1. Jika memilih role Admin
        if ($credentials['role'] === 'admin') {
            if (Auth::attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'role' => 'admin',
            ], $request->boolean('remember'))) {
                $request->session()->regenerate();

                return redirect()->intended(route('admin.dashboard'));
            }

            return back()->withErrors([
                'email' => 'Email atau password Admin tidak sesuai.',
            ])->withInput();
        }

        // 2. Jika memilih tab Kepala/Staff SPPG (role: 'sppg', 'kepala_sppg', 'staff')
        // Deteksi Otomatis 1: Cek apakah user adalah Kepala SPPG (tabel users)
        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'role' => 'kepala_sppg',
        ], $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->sppg && $user->sppg->status !== 'aktif') {
                Auth::logout();

                return back()->withErrors([
                    'sppg_status' => 'Akun SPPG masih menunggu verifikasi atau sudah dinonaktifkan.',
                ])->withInput();
            }

            $request->session()->regenerate();

            if (Route::has('sppg.dashboard')) {
                return redirect()->intended(route('sppg.dashboard'));
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        // Deteksi Otomatis 2: Cek apakah user adalah Staff (tabel staff)
        if (Auth::guard('staff')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $staff = Auth::guard('staff')->user();

            if ($staff->status !== 'aktif') {
                Auth::guard('staff')->logout();

                return back()->withErrors([
                    'sppg_status' => 'Akun Staff belum aktif atau sudah dinonaktifkan.',
                ])->withInput();
            }

            $request->session()->regenerate();

            if (Route::has('staff.dashboard')) {
                return redirect()->intended(route('staff.dashboard'));
            }

            if (Route::has('sppg.dashboard')) {
                return redirect()->intended(route('sppg.dashboard'));
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Email atau password SPPG tidak sesuai.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        if (Auth::guard('staff')->check()) {
            Auth::guard('staff')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
