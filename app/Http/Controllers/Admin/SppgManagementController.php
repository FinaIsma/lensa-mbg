<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Sppg;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SppgManagementController extends Controller
{
    public function index(Request $request)
    {
        // Dynamic counts directly from existing sppg table
        $totalCount = Sppg::count();
        $pendingCount = Sppg::whereIn('status_verifikasi', ['menunggu_verifikasi', 'perubahan'])->count();
        $activeCount = Sppg::where('status', 'aktif')->count();
        $inactiveCount = Sppg::where('status', 'nonaktif')->count();

        // Query Builder using user and staff relationship
        $query = Sppg::with(['user', 'staff', 'staffs']);

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
                    })
                    ->orWhereHas('staffs', function ($staffQuery) use ($search) {
                        $staffQuery->where('nama', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status Verif Dropdown Filter
        if ($request->filled('status_verif') && $request->input('status_verif') !== 'semua') {
            $statusVerif = $request->input('status_verif');
            if ($statusVerif === 'Terverifikasi') {
                $query->where('status_verifikasi', 'terverifikasi');
            } elseif ($statusVerif === 'Belum Diverifikasi') {
                $query->where('status_verifikasi', 'menunggu_verifikasi');
            } elseif ($statusVerif === 'Perubahan') {
                $query->where('status_verifikasi', 'perubahan');
            } elseif ($statusVerif === 'Ditolak') {
                $query->where('status_verifikasi', 'ditolak');
            }
        }

        // Stat Card Quick Click Filter
        if ($request->filled('card_filter')) {
            $card = $request->input('card_filter');
            if ($card === 'pending') {
                $query->whereIn('status_verifikasi', ['menunggu_verifikasi', 'perubahan']);
            } elseif ($card === 'aktif') {
                $query->where('status', 'aktif');
            } elseif ($card === 'nonaktif') {
                $query->where('status', 'nonaktif');
            }
        }

        // Paginate 7 items per page (ordered newest first)
        $sppgs = $query->orderByDesc('id_sppg')->paginate(7)->withQueryString();

        return view('admin.sppg-management', compact(
            'totalCount',
            'pendingCount',
            'activeCount',
            'inactiveCount',
            'sppgs'
        ));
    }

    public function show($id)
    {
        $sppg = Sppg::with(['user', 'staff', 'staffs'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sppg->id_sppg,
                'raw_id' => $sppg->id_sppg,
                'id_sppg' => 'SPPG'.str_pad($sppg->id_sppg, 3, '0', STR_PAD_LEFT),
                'nama_pegawai' => $sppg->user->nama ?? '-',
                'email' => $sppg->user->email ?? '-',
                'no_telepon' => $sppg->no_telepon ?? '-',
                'user_status' => $sppg->user->status ?? 'aktif',
                'staff_nama' => $sppg->staff->nama ?? '-',
                'staff_email' => $sppg->staff->email ?? '-',
                'staff_no_telepon' => $sppg->staff->no_telepon ?? '-',
                'staff_status' => $sppg->staff->status ?? '-',
                'staffs' => $sppg->staffs->map(fn ($st) => [
                    'id' => $st->id,
                    'nama' => $st->nama,
                    'email' => $st->email,
                    'no_telepon' => $st->no_telepon,
                    'status' => $st->status,
                    'catatan_admin' => $st->catatan_admin,
                ]),
                'nama_sppg' => $sppg->nama_sppg,
                'status' => $sppg->status,
                'status_verifikasi' => $sppg->status_verifikasi,
                'alamat_sppg' => $sppg->alamat_sppg ?? '-',
                'provinsi' => $sppg->provinsi ?? '-',
                'kabupaten_kota' => $sppg->kabupaten_kota ?? '-',
                'kecamatan' => $sppg->kecamatan ?? '-',
                'foto_ktp' => $sppg->foto_ktp ? (str_starts_with($sppg->foto_ktp, 'http') ? $sppg->foto_ktp : asset($sppg->foto_ktp)) : null,
                'foto_kantor_sppg' => $sppg->foto_kantor_sppg ? (str_starts_with($sppg->foto_kantor_sppg, 'http') ? $sppg->foto_kantor_sppg : asset($sppg->foto_kantor_sppg)) : null,
                'foto_surat_resmi' => $sppg->foto_surat_resmi ? (str_starts_with($sppg->foto_surat_resmi, 'http') ? $sppg->foto_surat_resmi : asset($sppg->foto_surat_resmi)) : null,
                'created_at' => $sppg->created_at ? $sppg->created_at->locale('id')->translatedFormat('d F Y') : '-',
            ],
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:aktif,nonaktif,menunggu_verifikasi'],
        ]);

        $sppg = Sppg::with(['user', 'staffs'])->findOrFail($id);
        $oldStatus = $sppg->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus || $sppg->status_verifikasi === 'menunggu_verifikasi') {
            DB::transaction(function () use ($sppg, $oldStatus, $newStatus) {
                if ($newStatus === 'aktif') {
                    $sppg->status = 'aktif';
                    $sppg->status_verifikasi = 'terverifikasi';
                    $sppg->save();

                    if ($sppg->user) {
                        $sppg->user->update(['status' => 'aktif']);
                    }

                    Staff::where('id_sppg', $sppg->id_sppg)
                        ->whereIn('status', ['menunggu', 'menunggu_persetujuan'])
                        ->update([
                            'status' => 'aktif',
                            'nonaktif_at' => null,
                            'nonaktif_reason' => null,
                        ]);

                    $aksi = ($oldStatus === 'aktif') ? 'sppg_activate' : 'sppg_verify';
                } elseif ($newStatus === 'nonaktif') {
                    $sppg->status = 'nonaktif';
                    if ($sppg->status_verifikasi === 'menunggu_verifikasi') {
                        $sppg->status_verifikasi = 'ditolak';
                        $aksi = 'sppg_reject';
                    } else {
                        $aksi = 'sppg_deactivate';
                    }
                    $sppg->save();

                    if ($sppg->user) {
                        $sppg->user->update(['status' => 'nonaktif']);
                    }

                    Staff::where('id_sppg', $sppg->id_sppg)
                        ->where('status', 'aktif')
                        ->update([
                            'status' => 'nonaktif',
                            'nonaktif_at' => now(),
                            'nonaktif_reason' => 'SPPG dinonaktifkan oleh Admin',
                        ]);
                } else {
                    $sppg->status = 'nonaktif';
                    $sppg->status_verifikasi = 'menunggu_verifikasi';
                    $sppg->save();
                    $aksi = 'sppg_deactivate';
                }

                AuditTrail::catat($aksi, $sppg, ['status' => $oldStatus], ['status' => $newStatus]);
            });
        }

        $idFormatted = 'SPPG'.str_pad((string) $sppg->id_sppg, 3, '0', STR_PAD_LEFT);
        $statusText = match ($validated['status']) {
            'aktif' => 'berhasil diverifikasi & diaktifkan.',
            'nonaktif' => 'telah ditolak / dinonaktifkan.',
            'menunggu_verifikasi' => 'dikembalikan ke status menunggu verifikasi.',
        };

        return back()->with('success', "Status {$idFormatted} ({$sppg->nama_sppg}) {$statusText}");
    }
}
