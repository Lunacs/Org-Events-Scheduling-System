<?php

use App\Http\Middleware\AutoCompleteTickets;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\CleanupStaleDrafts;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Email preview routes (development only)
            if (app()->environment('local')) {
                require __DIR__.'/../routes/email-preview.php';
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckUserRole::class,
        ]);

        $middleware->trustProxies(at: '*');

        // Auto-complete approved tickets (runs once per day via cache lock)
        // Replaces cron-based scheduler for hosts without cron support (e.g., Render free tier)
        $middleware->appendToGroup('web', [
            AutoCompleteTickets::class,
            CleanupStaleDrafts::class,
        ]);

        // Configure maintenance mode to allow SuperAdmin access
        $middleware->preventRequestsDuringMaintenance(except: [
            'superadmin*',  // Allow all SuperAdmin routes
            'up',           // Health check endpoint
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Use custom exception handler from App\Exceptions\Handler
        $exceptions->renderable(function (AuthorizationException $e, $request) {
            if (! $request->expectsJson()) {
                return response()->view('errors.403', ['exception' => $e], 403);
            }

            return null;
        });

        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Authentication required.',
                ], 401);
            }

            // Redirect to login so the remember-me cookie can re-authenticate the user
            return redirect()->guest(route('login'));
        });
    })->create();
