<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required', 'in:admin_sistem,admin_sppg'],
        ]);

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'role' => $credentials['role'],
        ])) {

            $request->session()->regenerate();

            if (Auth::user()->role === 'admin_sistem') {
                return redirect()->route('admin.dashboard');
            }

            if (Auth::user()->role === 'admin_sppg') {

                if (Auth::user()->sppg && Auth::user()->sppg->status !== 'aktif') {
                    Auth::logout();

                    return back()->withErrors([
                        'email' => 'Akun SPPG masih menunggu verifikasi atau sudah dinonaktifkan.',
                    ])->withInput();
                }

                return redirect()->route('sppg.dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Email, password, atau role tidak sesuai.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}