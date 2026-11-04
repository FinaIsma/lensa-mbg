<?php

namespace App\Http\Middleware;

use App\Models\Sppg;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user('staff') ?: $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $userRole = $user->role;

        // Validasi khusus Kepala SPPG: jika sedang menunggu verifikasi awal atau verifikasi perubahan profil
        if ($userRole === 'kepala_sppg') {
            $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();
            $hasPendingChange = false;
            if (Schema::hasTable('kepala_change_requests') && $sppg) {
                $hasPendingChange = DB::table('kepala_change_requests')
                    ->where('id_sppg', $sppg->id_sppg)
                    ->where('status', 'pending')
                    ->exists();
            }

            if (($sppg && $sppg->status_verifikasi === 'perubahan' && $hasPendingChange)
                || ($sppg && $sppg->status_verifikasi === 'menunggu_verifikasi')
                || $user->status !== 'aktif') {

                if (! $request->routeIs('sppg.profile.update')) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    $message = ($sppg && $sppg->status_verifikasi === 'perubahan') || $hasPendingChange
                        ? 'Pengajuan perubahan data profil SPPG Anda sedang dalam proses verifikasi oleh Admin Sistem. Anda belum dapat login sampai perubahan disetujui.'
                        : ($user->status === 'menunggu_verifikasi' || ($sppg && $sppg->status_verifikasi === 'menunggu_verifikasi')
                            ? 'Akun Kepala SPPG Anda masih menunggu verifikasi Admin.'
                            : 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.');

                    return redirect()->route('login')->withErrors(['sppg_status' => $message]);
                }
            }
        }

        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $subRole) {
                $trimmed = trim($subRole);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        if (! in_array($userRole, $allowedRoles, true)) {
            if ($userRole === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if ($userRole === 'kepala_sppg') {
                if (Route::has('sppg.dashboard')) {
                    return redirect()->route('sppg.dashboard');
                }
            }

            if ($userRole === 'staff') {
                if (Route::has('staff.dashboard')) {
                    return redirect()->route('staff.dashboard');
                }
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
