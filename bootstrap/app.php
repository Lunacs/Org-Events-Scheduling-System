<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
            'log.unauthorized' => \App\Http\Middleware\LogUnauthorizedAccess::class,
        ]);

        // Apply unauthorized access logging globally
        $middleware->append(\App\Http\Middleware\LogUnauthorizedAccess::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Use custom exception handler from App\Exceptions\Handler
        $exceptions->renderable(function (AuthorizationException $e, $request) {
            if (!$request->expectsJson()) {
                return response()->view('errors.403', ['exception' => $e], 403);
            }
            return null;
        });

        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if (!$request->expectsJson()) {
                return response()->view('errors.401', ['exception' => $e], 401);
            }
            return null;
        });
    })->create();
