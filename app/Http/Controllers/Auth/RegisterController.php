<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Sppg;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request, SupabaseStorageService $supabase)
    {
        foreach (['foto_ktp', 'foto_kantor_sppg', 'foto_surat_resmi'] as $field) {
            $file = $request->file($field);
            if ($file && !$file->isValid()) {
                \Illuminate\Support\Facades\Log::warning("File {$field} upload issue: " . $file->getErrorMessage() . " (Code: " . $file->getError() . ")");
            }
        }

        $validated = $request->validate([
            // Data Akun
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Data SPPG
            'nama_sppg' => ['required', 'string', 'max:255'],
            'alamat_sppg' => ['required', 'string'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kabupaten_kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],

            // Dokumen
            'foto_ktp' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'foto_kantor_sppg' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'foto_surat_resmi' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar dalam sistem.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nama_sppg.required' => 'Nama SPPG wajib diisi.',
            'alamat_sppg.required' => 'Alamat SPPG wajib diisi.',
            'provinsi.required' => 'Provinsi wajib dipilih.',
            'kabupaten_kota.required' => 'Kabupaten / Kota wajib dipilih.',
            'kecamatan.required' => 'Kecamatan wajib dipilih.',
            'foto_ktp.required' => 'Foto KTP wajib diunggah.',
            'foto_ktp.uploaded' => 'File KTP gagal diunggah. Pastikan ukuran file tidak melebihi 5 MB.',
            'foto_ktp.mimes' => 'Format file KTP harus berupa JPG atau PNG.',
            'foto_ktp.max' => 'Ukuran file KTP maksimal 5 MB.',
            'foto_kantor_sppg.required' => 'Foto kantor SPPG wajib diunggah.',
            'foto_kantor_sppg.uploaded' => 'Foto kantor gagal diunggah. Pastikan ukuran file tidak melebihi 5 MB.',
            'foto_kantor_sppg.mimes' => 'Format foto kantor harus berupa JPG atau PNG.',
            'foto_kantor_sppg.max' => 'Ukuran file foto kantor maksimal 5 MB.',
            'foto_surat_resmi.required' => 'Surat resmi / SK wajib diunggah.',
            'foto_surat_resmi.uploaded' => 'File surat resmi gagal diunggah. Pastikan ukuran file tidak melebihi 5 MB.',
            'foto_surat_resmi.mimes' => 'Format file surat resmi harus berupa PDF, JPG, atau PNG.',
            'foto_surat_resmi.max' => 'Ukuran file surat resmi maksimal 5 MB.',
            'syarat_ketentuan.accepted' => 'Anda harus menyetujui syarat dan ketentuan yang berlaku.',
        ]);

        try {
            DB::transaction(function () use ($request, $validated, $supabase) {
                // 1. Upload Files ke Supabase Storage (Bucket: register)
                $fotoKtpUrl = $supabase->upload($request->file('foto_ktp'), 'ktp', 'register');
                $fotoKantorUrl = $supabase->upload($request->file('foto_kantor_sppg'), 'kantor', 'register');
                $fotoSuratUrl = $supabase->upload($request->file('foto_surat_resmi'), 'surat', 'register');

                // 2. Create User
                $user = User::create([
                    'nama' => $validated['nama_lengkap'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'admin_sppg',
                ]);

                // 3. Create SPPG Profile
                Sppg::create([
                    'id_user' => $user->id_user,
                    'nama_sppg' => $validated['nama_sppg'],
                    'no_telepon' => $validated['no_telepon'],
                    'alamat_sppg' => $validated['alamat_sppg'],
                    'provinsi' => $validated['provinsi'],
                    'kabupaten_kota' => $validated['kabupaten_kota'],
                    'kecamatan' => $validated['kecamatan'],
                    'foto_ktp' => $fotoKtpUrl,
                    'foto_kantor_sppg' => $fotoKantorUrl,
                    'foto_surat_resmi' => $fotoSuratUrl,
                    'status' => 'menunggu_verifikasi',
                ]);
            });

            return redirect()->route('login')->with('success', 'Pendaftaran akun SPPG berhasil diajukan! Akun Anda sedang menunggu proses verifikasi oleh Admin Sistem.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi. (' . $e->getMessage() . ')']);
        }
    }
}
