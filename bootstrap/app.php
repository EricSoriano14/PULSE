<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Auth\Middleware\Authenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        /**
         * ✅ 1) Register middleware aliases used in routes like:
         * ->middleware('role:admin')
         */
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        /**
         * ✅ 2) Prevent cached pages (helps reduce 419 from stale CSRF forms)
         * Web only (does not affect API / Flutter)
         */
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\NoCache::class,
        ]);

        /**
         * ✅ 3) Ensure Authenticate runs BEFORE RoleMiddleware
         * so role middleware sees the authenticated user.
         */
        $middleware->appendToPriorityList(
            Authenticate::class,
            \App\Http\Middleware\RoleMiddleware::class
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
