<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sppg;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display the full User Management page for Admin Sistem.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Sppg::with('user');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('nama_sppg', $like, "%{$search}%")
                    ->orWhere('nama_pegawai', $like, "%{$search}%")
                    ->orWhere('alamat_sppg', $like, "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search, $like) {
                        $uq->where('nama', $like, "%{$search}%")
                            ->orWhere('email', $like, "%{$search}%");
                    });
            });
        }

        $sppgUsers = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total_user' => User::count(),
            'total_sppg' => Sppg::count(),
            'aktif' => Sppg::where('status', 'aktif')->count(),
            'menunggu' => Sppg::where('status', 'menunggu_verifikasi')->count(),
        ];

        return view('admin.users.index', compact('sppgUsers', 'stats', 'status', 'search'));
    }
}
