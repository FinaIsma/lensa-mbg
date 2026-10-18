<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        $middleware->redirectTo(
            guests: '/login',
            users: function (Request $request): string {
                $user = auth('staff')->user() ?: auth()->user();

                if ($user) {
                    if ($user->role === 'admin') {
                        return route('admin.dashboard');
                    }

                    if ($user->role === 'kepala_sppg' && Route::has('sppg.dashboard')) {
                        return route('sppg.dashboard');
                    }

                    if ($user->role === 'staff' && Route::has('staff.dashboard')) {
                        return route('staff.dashboard');
                    }
                }

                return route('admin.dashboard');
            }
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
