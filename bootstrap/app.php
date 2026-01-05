<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Sanctum ability aliases
        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability'   => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);

        // For API requests, do not redirect unauthenticated users to a "login" route.
        // Returning null here lets Laravel/Sanctum respond with a 401 JSON error instead.
        $middleware->redirectGuestsTo(function ($request) {
            // Always return JSON for API routes, regardless of Accept header
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }
            // For web routes, you can redirect to login if needed
            // Since this is API-only, return null for all cases
            return null;
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure API routes always return JSON, even without Accept header
        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
