<?php

namespace App\Http\Controllers\KepalaSPPG;

use App\Http\Controllers\Controller;
use App\Models\Sppg;
use App\Services\SupabaseStorageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Mengambil detail profil SPPG dan Kepala SPPG (JSON).
     */
    public function getProfile(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Pengguna belum terautentikasi.'], 401);
        }

        $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();

        $bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tglCreated = $sppg?->created_at ? Carbon::parse($sppg->created_at) : now();
        $tglFormatted = $tglCreated->format('d').' '.($bulanIndo[(int) $tglCreated->format('n')] ?? $tglCreated->format('F')).' '.$tglCreated->format('Y');

        $latestChangeRequest = null;
        if (Schema::hasTable('kepala_change_requests') && $sppg) {
            $latestChangeRequest = DB::table('kepala_change_requests')
                ->where('id_sppg', $sppg->id_sppg)
                ->latest('id')
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_user' => $user->id_user,
                'nama_lengkap' => $user->nama,
                'email' => $user->email,
                'no_telepon' => $sppg?->no_telepon ?? '',
                'id_sppg' => $sppg?->id_sppg,
                'id_sppg_formatted' => $sppg ? ('SPPG'.str_pad((string) $sppg->id_sppg, 3, '0', STR_PAD_LEFT)) : 'SPPG001',
                'nama_sppg' => $sppg?->nama_sppg ?? 'SPPG',
                'alamat_sppg' => $sppg?->alamat_sppg ?? '',
                'provinsi' => $sppg?->provinsi ?? '',
                'kabupaten_kota' => $sppg?->kabupaten_kota ?? '',
                'kecamatan' => $sppg?->kecamatan ?? '',
                'tanggal_dibuat' => $tglFormatted,
                'status' => $sppg?->status ?? 'aktif',
                'status_verifikasi' => $sppg?->status_verifikasi ?? 'terverifikasi',
                'foto_ktp' => $this->resolveUrl($sppg?->foto_ktp),
                'foto_kantor_sppg' => $this->resolveUrl($sppg?->foto_kantor_sppg),
                'foto_surat_resmi' => $this->resolveUrl($sppg?->foto_surat_resmi),
                'change_request' => $latestChangeRequest ? [
                    'status' => $latestChangeRequest->status,
                    'review_note' => $latestChangeRequest->review_note,
                    'requested_at' => $latestChangeRequest->requested_at,
                ] : null,
            ],
        ]);
    }

    private function resolveUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }

    /**
     * Memperbarui kata sandi Kepala SPPG.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Pengguna belum terautentikasi.'], 401);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'different:current_password'],
            'new_password_confirmation' => ['required', 'string', 'same:new_password'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal harus 8 karakter.',
            'new_password.different' => 'Password baru harus berbeda dengan password lama.',
            'new_password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'new_password_confirmation.same' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi lama yang Anda masukkan tidak sesuai.',
                'errors' => [
                    'current_password' => ['Kata sandi lama yang Anda masukkan tidak sesuai.'],
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui.',
        ]);
    }

    /**
     * Mengajukan perubahan profil Kepala SPPG & SPPG.
     */
    public function updateProfile(Request $request, SupabaseStorageService $supabase): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Pengguna belum terautentikasi.'], 401);
        }

        $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();
        if (! $sppg) {
            return response()->json(['success' => false, 'message' => 'Data SPPG tidak ditemukan.'], 404);
        }

        if ($sppg->status_verifikasi === 'perubahan' || (Schema::hasTable('kepala_change_requests') && DB::table('kepala_change_requests')->where('id_sppg', $sppg->id_sppg)->where('status', 'pending')->exists())) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan perubahan data Anda sedang dalam proses verifikasi oleh Admin Sistem. Anda belum dapat melakukan perubahan baru.',
            ], 422);
        }

        // Cek jika email baru sedang dalam antrean verifikasi oleh SPPG lain
        if (Schema::hasTable('kepala_change_requests') && DB::table('kepala_change_requests')
            ->where('id_sppg', '!=', $sppg->id_sppg)
            ->where('status', 'pending')
            ->where('email_baru', $request->input('email'))
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email ini sedang dalam proses verifikasi perubahan oleh akun SPPG lain.',
                'errors' => [
                    'email' => ['Email ini sedang dalam proses verifikasi perubahan oleh akun SPPG lain.'],
                ],
            ], 422);
        }

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                'unique:users,email,'.$user->id_user.',id_user',
                'unique:staff,email',
            ],
            'no_telepon' => ['required', 'string', 'max:30'],
            'nama_sppg' => ['required', 'string', 'max:150'],
            'alamat_sppg' => ['required', 'string'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kabupaten_kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'foto_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'foto_kantor_sppg' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'foto_surat_resmi' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap Kepala SPPG wajib diisi.',
            'email.required' => 'Email Kepala SPPG wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'nama_sppg.required' => 'Nama SPPG wajib diisi.',
            'alamat_sppg.required' => 'Alamat SPPG wajib diisi.',
            'provinsi.required' => 'Provinsi wajib dipilih.',
            'kabupaten_kota.required' => 'Kota/Kabupaten wajib dipilih.',
            'kecamatan.required' => 'Kecamatan wajib dipilih.',
            'foto_ktp.max' => 'Ukuran file Foto KTP maksimal 5 MB.',
            'foto_kantor_sppg.max' => 'Ukuran file Foto Kantor SPPG maksimal 5 MB.',
            'foto_surat_resmi.max' => 'Ukuran file Foto Surat Resmi maksimal 5 MB.',
        ]);

        try {
            DB::beginTransaction();

            $fotoKtpUrl = $sppg->foto_ktp;
            $fotoKantorUrl = $sppg->foto_kantor_sppg;
            $fotoSuratUrl = $sppg->foto_surat_resmi;

            if ($request->hasFile('foto_ktp')) {
                try {
                    $fotoKtpUrl = $supabase->upload($request->file('foto_ktp'), 'ktp', 'register');
                } catch (Exception $e) {
                    $file = $request->file('foto_ktp');
                    $filename = time().'_ktp_'.Str::random(8).'.'.$file->getClientOriginalExtension();
                    $file->move(public_path('uploads/documents'), $filename);
                    $fotoKtpUrl = asset('uploads/documents/'.$filename);
                }
            }

            if ($request->hasFile('foto_kantor_sppg')) {
                try {
                    $fotoKantorUrl = $supabase->upload($request->file('foto_kantor_sppg'), 'kantor', 'register');
                } catch (Exception $e) {
                    $file = $request->file('foto_kantor_sppg');
                    $filename = time().'_kantor_'.Str::random(8).'.'.$file->getClientOriginalExtension();
                    $file->move(public_path('uploads/documents'), $filename);
                    $fotoKantorUrl = asset('uploads/documents/'.$filename);
                }
            }

            if ($request->hasFile('foto_surat_resmi')) {
                try {
                    $fotoSuratUrl = $supabase->upload($request->file('foto_surat_resmi'), 'surat', 'register');
                } catch (Exception $e) {
                    $file = $request->file('foto_surat_resmi');
                    $filename = time().'_surat_'.Str::random(8).'.'.$file->getClientOriginalExtension();
                    $file->move(public_path('uploads/documents'), $filename);
                    $fotoSuratUrl = asset('uploads/documents/'.$filename);
                }
            }

            $provinsi = strcasecmp($validated['provinsi'], $sppg->provinsi ?? '') === 0
                ? $sppg->provinsi
                : Str::title(mb_strtolower($validated['provinsi']));

            $kabupaten = strcasecmp($validated['kabupaten_kota'], $sppg->kabupaten_kota ?? '') === 0
                ? $sppg->kabupaten_kota
                : Str::title(mb_strtolower($validated['kabupaten_kota']));

            $kecamatan = strcasecmp($validated['kecamatan'], $sppg->kecamatan ?? '') === 0
                ? $sppg->kecamatan
                : Str::title(mb_strtolower($validated['kecamatan']));

            // Simpan riwayat perubahan jika ada tabel kepala_change_requests
            if (Schema::hasTable('kepala_change_requests')) {
                DB::table('kepala_change_requests')->insert([
                    'id_sppg' => $sppg->id_sppg,
                    'requested_by' => $user->id_user,
                    'reason' => 'Perubahan data profil SPPG oleh Kepala SPPG',
                    'nama_baru' => $validated['nama_lengkap'],
                    'email_baru' => $validated['email'],
                    'no_telepon_baru' => $validated['no_telepon'],
                    'foto_ktp_baru' => $fotoKtpUrl,
                    'foto_surat_baru' => $fotoSuratUrl,
                    'old_values' => json_encode([
                        'nama' => $user->nama,
                        'email' => $user->email,
                        'nama_sppg' => $sppg->nama_sppg,
                        'no_telepon' => $sppg->no_telepon,
                        'alamat_sppg' => $sppg->alamat_sppg,
                        'provinsi' => $sppg->provinsi,
                        'kabupaten_kota' => $sppg->kabupaten_kota,
                        'kecamatan' => $sppg->kecamatan,
                        'foto_ktp' => $sppg->foto_ktp,
                        'foto_kantor_sppg' => $sppg->foto_kantor_sppg,
                        'foto_surat_resmi' => $sppg->foto_surat_resmi,
                        'proposed' => [
                            'nama' => $validated['nama_lengkap'],
                            'email' => $validated['email'],
                            'no_telepon' => $validated['no_telepon'],
                            'nama_sppg' => $validated['nama_sppg'],
                            'alamat_sppg' => $validated['alamat_sppg'],
                            'provinsi' => $provinsi,
                            'kabupaten_kota' => $kabupaten,
                            'kecamatan' => $kecamatan,
                            'foto_ktp' => $fotoKtpUrl,
                            'foto_kantor_sppg' => $fotoKantorUrl,
                            'foto_surat_resmi' => $fotoSuratUrl,
                        ],
                    ]),
                    'status' => 'pending',
                    'requested_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // STAGING PATTERN:
            // Data utama pada tabel users dan sppg TIDAK diubah sekarang!
            // Hanya status_verifikasi yang diubah menjadi 'perubahan' agar terkunci dan masuk ke antrean verifikasi Admin.
            $sppg->update([
                'status_verifikasi' => 'perubahan',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan perubahan data berhasil dikirim dan akan diverifikasi oleh Admin Sistem.',
                'data' => [
                    'nama_lengkap' => $validated['nama_lengkap'],
                    'email' => $validated['email'],
                    'no_telepon' => $validated['no_telepon'],
                    'nama_sppg' => $validated['nama_sppg'],
                    'alamat_sppg' => $validated['alamat_sppg'],
                    'provinsi' => $provinsi,
                    'kabupaten_kota' => $kabupaten,
                    'kecamatan' => $kecamatan,
                    'foto_ktp' => $fotoKtpUrl,
                    'foto_kantor_sppg' => $fotoKantorUrl,
                    'foto_surat_resmi' => $fotoSuratUrl,
                ],
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengajukan perubahan profil: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan perubahan: '.$e->getMessage(),
            ], 500);
        }
    }
}
