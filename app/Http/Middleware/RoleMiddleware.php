<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
