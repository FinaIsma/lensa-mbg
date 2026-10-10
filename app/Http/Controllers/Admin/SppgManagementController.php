<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sppg;
use Illuminate\Http\Request;

class SppgManagementController extends Controller
{
    public function index(Request $request)
    {
        // Dynamic counts directly from existing sppg table
        $totalCount = Sppg::count();
        $pendingCount = Sppg::where('status', 'menunggu_verifikasi')->count();
        $activeCount = Sppg::where('status', 'aktif')->count();
        $inactiveCount = Sppg::where('status', 'nonaktif')->count();

        // Query Builder using user relationship
        $query = Sppg::with('user');

        // Search Filter (nama pegawai, email, sppg name, or address)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_sppg', 'like', "%{$search}%")
                    ->orWhere('alamat_sppg', 'like', "%{$search}%")
                    ->orWhere('provinsi', 'like', "%{$search}%")
                    ->orWhere('kabupaten_kota', 'like', "%{$search}%")
                    ->orWhere('kecamatan', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('nama', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status Verif Dropdown Filter
        if ($request->filled('status_verif') && $request->input('status_verif') !== 'semua') {
            $statusVerif = $request->input('status_verif');
            if ($statusVerif === 'Terverifikasi') {
                $query->where('status', 'aktif');
            } elseif ($statusVerif === 'Belum Diverifikasi') {
                $query->where('status', 'menunggu_verifikasi');
            } elseif ($statusVerif === 'Ditolak') {
                $query->where('status', 'nonaktif');
            }
        }

        // Stat Card Quick Click Filter
        if ($request->filled('card_filter')) {
            $card = $request->input('card_filter');
            if ($card === 'pending') {
                $query->where('status', 'menunggu_verifikasi');
            } elseif ($card === 'aktif') {
                $query->where('status', 'aktif');
            } elseif ($card === 'nonaktif') {
                $query->where('status', 'nonaktif');
            }
        }

        // Paginate 7 items per page
        $sppgs = $query->orderBy('id_sppg', 'asc')->paginate(7)->withQueryString();

        return view('admin.sppg_management', compact(
            'totalCount',
            'pendingCount',
            'activeCount',
            'inactiveCount',
            'sppgs'
        ));
    }

    public function show($id)
    {
        $sppg = Sppg::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id_sppg' => 'SPPG'.str_pad($sppg->id_sppg, 3, '0', STR_PAD_LEFT),
                'nama_pegawai' => $sppg->user->nama ?? '-',
                'email' => $sppg->user->email ?? '-',
                'nama_sppg' => $sppg->nama_sppg,
                'status' => $sppg->status,
                'no_telepon' => $sppg->no_telepon ?? '-',
                'alamat_sppg' => $sppg->alamat_sppg ?? '-',
                'provinsi' => $sppg->provinsi ?? '-',
                'kabupaten_kota' => $sppg->kabupaten_kota ?? '-',
                'kecamatan' => $sppg->kecamatan ?? '-',
                'foto_ktp' => $sppg->foto_ktp,
                'foto_kantor_sppg' => $sppg->foto_kantor_sppg,
                'foto_surat_resmi' => $sppg->foto_surat_resmi,
                'created_at' => $sppg->created_at ? $sppg->created_at->format('d M Y, H:i') : '-',
            ],
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:aktif,nonaktif,menunggu_verifikasi'],
        ]);

        $sppg = Sppg::findOrFail($id);
        $sppg->status = $validated['status'];
        $sppg->save();

        $idFormatted = 'SPPG'.str_pad($sppg->id_sppg, 3, '0', STR_PAD_LEFT);
        $statusText = match ($validated['status']) {
            'aktif' => 'berhasil diverifikasi & diaktifkan.',
            'nonaktif' => 'telah ditolak / dinonaktifkan.',
            'menunggu_verifikasi' => 'dikembalikan ke status menunggu verifikasi.',
        };

        return back()->with('success', "Status {$idFormatted} ({$sppg->nama_sppg}) {$statusText}");
    }
}
