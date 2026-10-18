<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function showRegister()
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

        return view('auth.register');
    }

    public function checkEmail(Request $request)
    {
        $email = trim($request->query('email', ''));
        if (empty($email)) {
            return response()->json(['available' => true]);
        }

        $existsInUsers = User::where('email', $email)->exists();
        $existsInStaff = Staff::where('email', $email)->exists();

        $exists = $existsInUsers || $existsInStaff;

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Email sudah terdaftar dalam sistem.' : null,
        ]);
    }

    public function register(Request $request, SupabaseStorageService $supabase)
    {
        foreach (['foto_ktp', 'foto_kantor_sppg', 'foto_surat_resmi'] as $field) {
            $file = $request->file($field);
            if ($file && ! $file->isValid()) {
                Log::warning("File {$field} upload issue: ".$file->getErrorMessage().' (Code: '.$file->getError().')');
            }
        }

        $validated = $request->validate([
            // 1. Data Akun Kepala SPPG
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'unique:staff,email', 'different:staff_email'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // 2. Data Akun Staff SPPG
            'staff_nama_lengkap' => ['required', 'string', 'max:150'],
            'staff_email' => ['required', 'string', 'email', 'max:150', 'unique:staff,email', 'unique:users,email', 'different:email'],
            'staff_no_telepon' => ['required', 'string', 'max:30'],
            'staff_password' => ['required', 'string', 'min:8', 'confirmed'],

            // 3. Data SPPG
            'nama_sppg' => ['required', 'string', 'max:255'],
            'alamat_sppg' => ['required', 'string'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kabupaten_kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],

            // 4. Dokumen
            'foto_ktp' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'foto_kantor_sppg' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'foto_surat_resmi' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'syarat_ketentuan' => ['accepted'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap Kepala SPPG wajib diisi.',
            'email.required' => 'Email Kepala SPPG wajib diisi.',
            'email.email' => 'Format email Kepala SPPG tidak valid.',
            'email.unique' => 'Email Kepala SPPG sudah terdaftar dalam sistem.',
            'email.different' => 'Email Kepala SPPG tidak boleh sama dengan email Staff SPPG.',
            'no_telepon.required' => 'Nomor telepon Kepala SPPG wajib diisi.',
            'password.required' => 'Password Kepala SPPG wajib diisi.',
            'password.min' => 'Password Kepala SPPG minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password Kepala SPPG tidak cocok.',

            'staff_nama_lengkap.required' => 'Nama lengkap Staff SPPG wajib diisi.',
            'staff_email.required' => 'Email Staff SPPG wajib diisi.',
            'staff_email.email' => 'Format email Staff SPPG tidak valid.',
            'staff_email.unique' => 'Email Staff SPPG sudah terdaftar dalam sistem.',
            'staff_email.different' => 'Email Staff SPPG tidak boleh sama dengan email Kepala SPPG.',
            'staff_no_telepon.required' => 'Nomor telepon Staff SPPG wajib diisi.',
            'staff_password.required' => 'Password Staff SPPG wajib diisi.',
            'staff_password.min' => 'Password Staff SPPG minimal harus 8 karakter.',
            'staff_password.confirmed' => 'Konfirmasi password Staff SPPG tidak cocok.',

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

                // 2. Create User (Kepala SPPG)
                $user = User::create([
                    'nama' => $validated['nama_lengkap'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'kepala_sppg',
                ]);

                // 3. Create SPPG Profile
                $sppg = Sppg::create([
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

                // 4. Create Staff SPPG with FK id_sppg
                Staff::create([
                    'id_sppg' => $sppg->id_sppg,
                    'nama' => $validated['staff_nama_lengkap'],
                    'email' => $validated['staff_email'],
                    'password' => Hash::make($validated['staff_password']),
                    'no_telepon' => $validated['staff_no_telepon'],
                    'status' => 'menunggu',
                ]);
            });

            $successMessage = 'Pendaftaran akun SPPG berhasil diajukan! Akun Anda sedang menunggu proses verifikasi oleh Admin Sistem.';

            if ($request->expectsJson() || $request->ajax()) {
                session()->flash('success', $successMessage);

                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'redirect' => route('login', ['role' => 'kepala_sppg']),
                ]);
            }

            return redirect()->route('login', ['role' => 'kepala_sppg'])->with('success', $successMessage);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi.',
                    'errors' => [
                        'general' => ['Terjadi kesalahan saat memproses pendaftaran: '.$e->getMessage()],
                    ],
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['general' => 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi. ('.$e->getMessage().')']);
        }
    }
}
