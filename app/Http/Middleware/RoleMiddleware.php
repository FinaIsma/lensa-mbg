<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->role !== $role) {
            // Redirect to their appropriate dashboard instead of 403
            if ($request->user()->role === 'admin_sistem') {
                return redirect()->route('admin.dashboard');
            }
            if ($request->user()->role === 'admin_sppg') {
                return redirect()->route('sppg.dashboard');
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}
