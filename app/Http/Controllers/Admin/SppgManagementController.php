<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Sppg;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SppgManagementController extends Controller
{
    public function index(Request $request)
    {
        // Dynamic counts directly from existing sppg table
        $totalCount = Sppg::count();
        $pendingCount = Sppg::whereIn('status_verifikasi', ['menunggu_verifikasi', 'perubahan'])->count();
        $activeCount = Sppg::where('status', 'aktif')->count();
        $inactiveCount = Sppg::where('status', 'nonaktif')
            ->whereNotIn('status_verifikasi', ['menunggu_verifikasi', 'perubahan'])
            ->count();

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
                $query->where('status', 'nonaktif')
                    ->whereNotIn('status_verifikasi', ['menunggu_verifikasi', 'perubahan']);
            }
        }

        // Paginate 7 items per page (ordered newest first)
        $sppgs = $query->orderByDesc('id_sppg')->paginate(7)->withQueryString();

        if (Schema::hasTable('kepala_change_requests')) {
            $sppgIds = $sppgs->pluck('id_sppg');
            $pendingChanges = DB::table('kepala_change_requests')
                ->whereIn('id_sppg', $sppgIds)
                ->where('status', 'pending')
                ->get()
                ->keyBy('id_sppg');

            foreach ($sppgs as $item) {
                if ($change = $pendingChanges->get($item->id_sppg)) {
                    $rawOldValues = json_decode($change->old_values, true) ?? [];
                    $proposed = $rawOldValues['proposed'] ?? [];

                    $item->proposed = [
                        'nama_pegawai' => $proposed['nama'] ?? $change->nama_baru,
                        'email' => $proposed['email'] ?? $change->email_baru,
                        'no_telepon' => $proposed['no_telepon'] ?? $change->no_telepon_baru,
                        'nama_sppg' => $proposed['nama_sppg'] ?? $item->nama_sppg,
                        'alamat_sppg' => $proposed['alamat_sppg'] ?? $item->alamat_sppg,
                        'provinsi' => $proposed['provinsi'] ?? $item->provinsi,
                        'kabupaten_kota' => $proposed['kabupaten_kota'] ?? $item->kabupaten_kota,
                        'kecamatan' => $proposed['kecamatan'] ?? $item->kecamatan,
                        'foto_ktp' => ! empty($proposed['foto_ktp'])
                            ? (str_starts_with($proposed['foto_ktp'], 'http') ? $proposed['foto_ktp'] : asset($proposed['foto_ktp']))
                            : ($change->foto_ktp_baru ? (str_starts_with($change->foto_ktp_baru, 'http') ? $change->foto_ktp_baru : asset($change->foto_ktp_baru)) : null),
                        'foto_kantor_sppg' => ! empty($proposed['foto_kantor_sppg'])
                            ? (str_starts_with($proposed['foto_kantor_sppg'], 'http') ? $proposed['foto_kantor_sppg'] : asset($proposed['foto_kantor_sppg']))
                            : null,
                        'foto_surat_resmi' => ! empty($proposed['foto_surat_resmi'])
                            ? (str_starts_with($proposed['foto_surat_resmi'], 'http') ? $proposed['foto_surat_resmi'] : asset($proposed['foto_surat_resmi']))
                            : ($change->foto_surat_baru ? (str_starts_with($change->foto_surat_baru, 'http') ? $change->foto_surat_baru : asset($change->foto_surat_baru)) : null),
                    ];

                    $item->before_values = [
                        'nama_pegawai' => $item->user?->nama ?? $rawOldValues['nama'] ?? null,
                        'email' => $item->user?->email ?? $rawOldValues['email'] ?? null,
                        'no_telepon' => $item->no_telepon ?? $rawOldValues['no_telepon'] ?? null,
                        'nama_sppg' => $item->nama_sppg ?? $rawOldValues['nama_sppg'] ?? null,
                        'alamat_sppg' => $item->alamat_sppg ?? $rawOldValues['alamat_sppg'] ?? null,
                        'provinsi' => $item->provinsi ?? $rawOldValues['provinsi'] ?? null,
                        'kabupaten_kota' => $item->kabupaten_kota ?? $rawOldValues['kabupaten_kota'] ?? null,
                        'kecamatan' => $item->kecamatan ?? $rawOldValues['kecamatan'] ?? null,
                        'foto_ktp' => ! empty($item->foto_ktp)
                            ? (str_starts_with($item->foto_ktp, 'http') ? $item->foto_ktp : asset($item->foto_ktp))
                            : (! empty($rawOldValues['foto_ktp']) ? (str_starts_with($rawOldValues['foto_ktp'], 'http') ? $rawOldValues['foto_ktp'] : asset($rawOldValues['foto_ktp'])) : null),
                        'foto_kantor_sppg' => ! empty($item->foto_kantor_sppg)
                            ? (str_starts_with($item->foto_kantor_sppg, 'http') ? $item->foto_kantor_sppg : asset($item->foto_kantor_sppg))
                            : (! empty($rawOldValues['foto_kantor_sppg']) ? (str_starts_with($rawOldValues['foto_kantor_sppg'], 'http') ? $rawOldValues['foto_kantor_sppg'] : asset($rawOldValues['foto_kantor_sppg'])) : null),
                        'foto_surat_resmi' => ! empty($item->foto_surat_resmi)
                            ? (str_starts_with($item->foto_surat_resmi, 'http') ? $item->foto_surat_resmi : asset($item->foto_surat_resmi))
                            : (! empty($rawOldValues['foto_surat_resmi']) ? (str_starts_with($rawOldValues['foto_surat_resmi'], 'http') ? $rawOldValues['foto_surat_resmi'] : asset($rawOldValues['foto_surat_resmi'])) : null),
                    ];
                }
            }
        }

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
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $sppg = Sppg::with(['user', 'staffs'])->findOrFail($id);
        $oldStatus = $sppg->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus || in_array($sppg->status_verifikasi, ['menunggu_verifikasi', 'perubahan'], true)) {
            $isPerubahan = $sppg->status_verifikasi === 'perubahan';
            DB::transaction(function () use ($sppg, $oldStatus, $newStatus, $request, $isPerubahan) {
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

                    if ($isPerubahan && Schema::hasTable('kepala_change_requests')) {
                        $pendingCr = DB::table('kepala_change_requests')
                            ->where('id_sppg', $sppg->id_sppg)
                            ->where('status', 'pending')
                            ->first();

                        if ($pendingCr) {
                            $rawOldValues = json_decode($pendingCr->old_values, true) ?? [];
                            $proposed = $rawOldValues['proposed'] ?? [];

                            // Terapkan perubahan ke akun User (Kepala SPPG)
                            $userUpdate = [];
                            if (! empty($proposed['nama'])) {
                                $userUpdate['nama'] = $proposed['nama'];
                            } elseif (! empty($pendingCr->nama_baru)) {
                                $userUpdate['nama'] = $pendingCr->nama_baru;
                            }
                            if (! empty($proposed['email'])) {
                                $userUpdate['email'] = $proposed['email'];
                            } elseif (! empty($pendingCr->email_baru)) {
                                $userUpdate['email'] = $pendingCr->email_baru;
                            }
                            if (! empty($userUpdate) && $sppg->user) {
                                $sppg->user->update($userUpdate);
                            }

                            // Terapkan perubahan ke data SPPG
                            $sppgUpdate = [];
                            if (! empty($proposed['nama_sppg'])) {
                                $sppgUpdate['nama_sppg'] = $proposed['nama_sppg'];
                            }
                            if (! empty($proposed['no_telepon'])) {
                                $sppgUpdate['no_telepon'] = $proposed['no_telepon'];
                            } elseif (! empty($pendingCr->no_telepon_baru)) {
                                $sppgUpdate['no_telepon'] = $pendingCr->no_telepon_baru;
                            }
                            if (! empty($proposed['alamat_sppg'])) {
                                $sppgUpdate['alamat_sppg'] = $proposed['alamat_sppg'];
                            }
                            if (! empty($proposed['provinsi'])) {
                                $sppgUpdate['provinsi'] = $proposed['provinsi'];
                            }
                            if (! empty($proposed['kabupaten_kota'])) {
                                $sppgUpdate['kabupaten_kota'] = $proposed['kabupaten_kota'];
                            }
                            if (! empty($proposed['kecamatan'])) {
                                $sppgUpdate['kecamatan'] = $proposed['kecamatan'];
                            }
                            if (! empty($proposed['foto_ktp'])) {
                                $sppgUpdate['foto_ktp'] = $proposed['foto_ktp'];
                            } elseif (! empty($pendingCr->foto_ktp_baru)) {
                                $sppgUpdate['foto_ktp'] = $pendingCr->foto_ktp_baru;
                            }
                            if (! empty($proposed['foto_kantor_sppg'])) {
                                $sppgUpdate['foto_kantor_sppg'] = $proposed['foto_kantor_sppg'];
                            }
                            if (! empty($proposed['foto_surat_resmi'])) {
                                $sppgUpdate['foto_surat_resmi'] = $proposed['foto_surat_resmi'];
                            } elseif (! empty($pendingCr->foto_surat_baru)) {
                                $sppgUpdate['foto_surat_resmi'] = $pendingCr->foto_surat_baru;
                            }

                            if (! empty($sppgUpdate)) {
                                $sppg->update($sppgUpdate);
                            }

                            DB::table('kepala_change_requests')
                                ->where('id', $pendingCr->id)
                                ->update([
                                    'status' => 'approved',
                                    'reviewed_by' => auth()->id(),
                                    'reviewed_at' => now(),
                                    'review_note' => $request->input('review_note') ?: 'Pengajuan perubahan disetujui.',
                                    'updated_at' => now(),
                                ]);
                        }
                    } elseif (Schema::hasTable('kepala_change_requests')) {
                        DB::table('kepala_change_requests')
                            ->where('id_sppg', $sppg->id_sppg)
                            ->where('status', 'pending')
                            ->update([
                                'status' => 'approved',
                                'reviewed_by' => auth()->id(),
                                'reviewed_at' => now(),
                                'review_note' => $request->input('review_note') ?: 'Pengajuan perubahan disetujui.',
                                'updated_at' => now(),
                            ]);
                    }

                    $aksi = $isPerubahan ? 'kepala_change_approve' : (($oldStatus === 'aktif') ? 'sppg_activate' : 'sppg_verify');
                } elseif ($newStatus === 'nonaktif') {
                    $sppg->status = 'nonaktif';
                    if ($sppg->status_verifikasi === 'menunggu_verifikasi') {
                        $sppg->status_verifikasi = 'ditolak';
                        $aksi = 'sppg_reject';
                    } elseif ($sppg->status_verifikasi === 'perubahan') {
                        $sppg->status_verifikasi = 'terverifikasi';
                        $sppg->status = 'aktif';
                        $aksi = 'kepala_change_reject';

                        if (Schema::hasTable('kepala_change_requests')) {
                            $pendingCr = DB::table('kepala_change_requests')
                                ->where('id_sppg', $sppg->id_sppg)
                                ->where('status', 'pending')
                                ->first();

                            if ($pendingCr) {
                                // Kompatibilitas mundur: jika data diajukan dengan pola lama (tanpa proposed), pulihkan nilai lama
                                $oldValues = json_decode($pendingCr->old_values, true) ?? [];
                                if (! empty($oldValues) && ! isset($oldValues['proposed'])) {
                                    if ($sppg->user && isset($oldValues['nama'])) {
                                        $sppg->user->update([
                                            'nama' => $oldValues['nama'],
                                            'email' => $oldValues['email'] ?? $sppg->user->email,
                                        ]);
                                    }
                                    $sppgUpdate = [
                                        'nama_sppg' => $oldValues['nama_sppg'] ?? $sppg->nama_sppg,
                                        'no_telepon' => $oldValues['no_telepon'] ?? $sppg->no_telepon,
                                        'alamat_sppg' => $oldValues['alamat_sppg'] ?? $sppg->alamat_sppg,
                                        'provinsi' => $oldValues['provinsi'] ?? $sppg->provinsi,
                                        'kabupaten_kota' => $oldValues['kabupaten_kota'] ?? $sppg->kabupaten_kota,
                                        'kecamatan' => $oldValues['kecamatan'] ?? $sppg->kecamatan,
                                    ];
                                    if (isset($oldValues['foto_ktp'])) {
                                        $sppgUpdate['foto_ktp'] = $oldValues['foto_ktp'];
                                    }
                                    if (isset($oldValues['foto_kantor_sppg'])) {
                                        $sppgUpdate['foto_kantor_sppg'] = $oldValues['foto_kantor_sppg'];
                                    }
                                    if (isset($oldValues['foto_surat_resmi'])) {
                                        $sppgUpdate['foto_surat_resmi'] = $oldValues['foto_surat_resmi'];
                                    }
                                    $sppg->update($sppgUpdate);
                                }

                                DB::table('kepala_change_requests')
                                    ->where('id', $pendingCr->id)
                                    ->update([
                                        'status' => 'rejected',
                                        'reviewed_by' => auth()->id(),
                                        'reviewed_at' => now(),
                                        'review_note' => $request->input('review_note') ?: 'Pengajuan perubahan ditolak oleh Administrator.',
                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    } else {
                        $aksi = 'sppg_deactivate';
                    }
                    $sppg->save();

                    if (! $isPerubahan) {
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
                        if ($sppg->user) {
                            $sppg->user->update(['status' => 'aktif']);
                        }
                    }
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
            'nonaktif' => $isPerubahan ? 'pengajuan perubahannya telah ditolak (data SPPG tetap aktif).' : 'telah ditolak / dinonaktifkan.',
            'menunggu_verifikasi' => 'dikembalikan ke status menunggu verifikasi.',
        };

        return back()->with('success', "Status {$idFormatted} ({$sppg->nama_sppg}) {$statusText}");
    }
}
