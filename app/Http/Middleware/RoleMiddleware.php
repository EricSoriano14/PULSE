<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Not logged in
        if (!$user) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        $userRole = strtolower(trim((string) ($user->role ?? '')));
        $allowed = array_map(
            fn ($r) => strtolower(trim((string) $r)),
            $roles
        );

        if ($userRole === '' || !in_array($userRole, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
