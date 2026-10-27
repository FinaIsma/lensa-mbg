<?php

namespace App\Http\Controllers\KepalaSPPG;

use App\Http\Controllers\Controller;
use App\Models\Sppg;
use App\Models\StaffManagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffManagementController extends Controller
{
    /**
     * Tampilkan daftar staff manajemen khusus untuk SPPG yang dipimpin.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();
        $idSppg = $sppg?->id_sppg;

        if (! $idSppg) {
            $emptyPaginator = new LengthAwarePaginator([], 0, 7, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return view('KepalaSPPG.staff-management', [
                'staffs' => $emptyPaginator,
                'totalStaff' => 0,
                'activeStaff' => 0,
                'pendingStaff' => 0,
                'inactiveStaff' => 0,
                'sppg' => null,
            ]);
        }

        $baseQuery = StaffManagement::where('id_sppg', $idSppg);

        // Dynamic counts untuk 4 stat card
        $totalStaff = (clone $baseQuery)->count();
        $activeStaff = (clone $baseQuery)->where('status', 'aktif')->count();
        $pendingStaff = (clone $baseQuery)->whereIn('status', ['menunggu', 'menunggu_persetujuan'])->count();
        $inactiveStaff = (clone $baseQuery)->where('status', 'nonaktif')->count();

        // Query data tabel dengan filter
        $query = (clone $baseQuery);

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telepon', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status_verif') && $request->query('status_verif') !== 'semua') {
            $status = $request->query('status_verif');
            if ($status === 'menunggu_persetujuan' || $status === 'menunggu' || $status === 'Belum Diverifikasi') {
                $query->whereIn('status', ['menunggu', 'menunggu_persetujuan']);
            } elseif ($status === 'aktif' || $status === 'Terverifikasi') {
                $query->where('status', 'aktif');
            } elseif ($status === 'nonaktif') {
                $query->where('status', 'nonaktif');
            } elseif ($status === 'ditolak' || $status === 'Ditolak') {
                $query->where('status', 'ditolak');
            }
        }

        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        $staffs = $query->paginate(7)->withQueryString();

        return view('KepalaSPPG.staff-management', [
            'staffs' => $staffs,
            'totalStaff' => $totalStaff,
            'activeStaff' => $activeStaff,
            'pendingStaff' => $pendingStaff,
            'inactiveStaff' => $inactiveStaff,
            'sppg' => $sppg,
        ]);
    }

    /**
     * Ajukan penambahan staff baru untuk diverifikasi admin sistem.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();

        if (! $sppg) {
            return redirect()->route('sppg.staff.index')->with('error', 'Data SPPG tidak ditemukan.');
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:staff,email', 'unique:users,email'],
            'no_telepon' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nama.required' => 'Nama lengkap staff wajib diisi.',
            'email.required' => 'Email staff wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'no_telepon.required' => 'Nomor telepon staff wajib diisi.',
            'password.required' => 'Password staff wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        StaffManagement::create([
            'id_sppg' => $sppg->id_sppg,
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'no_telepon' => $validated['no_telepon'],
            'status' => 'menunggu_persetujuan',
        ]);

        if ($sppg->status_verifikasi === 'terverifikasi' || $sppg->status === 'aktif') {
            $sppg->update([
                'status_verifikasi' => 'perubahan',
            ]);
        }

        return redirect()->route('sppg.staff.index')->with('success', 'Pengajuan penambahan staff berhasil dikirim dan menunggu verifikasi Admin Sistem.');
    }

    /**
     * Mengambil detail staff untuk modal detail (AJAX).
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();

        if (! $sppg) {
            return response()->json(['message' => 'SPPG tidak ditemukan'], 404);
        }

        $staff = StaffManagement::where('id_sppg', $sppg->id_sppg)->find($id);

        if (! $staff) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        $bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tglCreated = $staff->created_at ? Carbon::parse($staff->created_at) : now();
        $tglFormatted = $tglCreated->format('d').' '.($bulanIndo[(int) $tglCreated->format('n')] ?? $tglCreated->format('F')).' '.$tglCreated->format('Y');

        return response()->json([
            'id' => $staff->id,
            'nama' => $staff->nama,
            'email' => $staff->email,
            'no_telepon' => $staff->no_telepon ?? '-',
            'status' => $staff->status,
            'status_label' => match ($staff->status) {
                'aktif' => 'Aktif',
                'menunggu', 'menunggu_persetujuan' => 'Menunggu Verifikasi',
                'ditolak' => 'Ditolak',
                default => 'Nonaktif',
            },
            'sppg_nama' => $sppg->nama_sppg,
            'sppg_id_formatted' => 'SPPG'.str_pad((string) $sppg->id_sppg, 3, '0', STR_PAD_LEFT),
            'sppg_alamat' => $sppg->alamat_sppg ?? '-',
            'sppg_provinsi' => $sppg->provinsi ?? '-',
            'sppg_kabupaten' => $sppg->kabupaten_kota ?? '-',
            'sppg_kecamatan' => $sppg->kecamatan ?? '-',
            'tanggal_dibuat' => $tglFormatted,
            'nama_kepala' => $sppg->user->nama ?? $user->nama ?? '-',
        ]);
    }

    /**
     * Menonaktifkan akun staff.
     */
    public function deactivate(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();

        if (! $sppg) {
            return redirect()->route('sppg.staff.index')->with('error', 'Data SPPG tidak ditemukan.');
        }

        $staff = StaffManagement::where('id_sppg', $sppg->id_sppg)->find($id);

        if (! $staff) {
            return redirect()->route('sppg.staff.index')->with('error', 'Data staff tidak ditemukan.');
        }

        if ($staff->status === 'nonaktif') {
            return redirect()->route('sppg.staff.index')->with('info', 'Staff ini sudah dalam status nonaktif.');
        }

        $reason = $request->input('reason', 'Dinonaktifkan oleh Kepala SPPG');

        $staff->update([
            'status' => 'nonaktif',
            'nonaktif_at' => now(),
            'nonaktif_reason' => $reason,
        ]);

        return redirect()->route('sppg.staff.index')->with('success', "Akun staff {$staff->nama} berhasil dinonaktifkan.");
    }
}
