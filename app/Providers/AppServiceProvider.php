<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ Fix: When an already-authenticated user visits /login,
        // don't force them to /dashboard (admin-only).
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = $request->user();

            if (!$user) {
                return route('login');
            }

            $role = strtolower(trim((string) ($user->role ?? '')));

            if ($role === 'admin') {
                return route('dashboard');
            }

            if (in_array($role, ['css', 'faculty'], true)) {
                return route('receive-report');
            }

            // Students / unknown roles:
            return route('login');
        });
    }
}
