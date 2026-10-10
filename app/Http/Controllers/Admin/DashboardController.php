<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sppg;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSppg = Sppg::count();
        $pendingVerif = Sppg::where('status', 'menunggu_verifikasi')->count();
        $activeSppg = Sppg::where('status', 'aktif')->count();
        $inactiveSppg = Sppg::where('status', 'nonaktif')->count();
        $totalUsers = User::count();

        // Fetch recent SPPG registrations
        $recentSppg = Sppg::with('user')
            ->orderBy('id_sppg', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalSppg',
            'pendingVerif',
            'activeSppg',
            'inactiveSppg',
            'totalUsers',
            'recentSppg'
        ));
    }
}
