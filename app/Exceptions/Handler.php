<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction_Logs;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle Authorization Exceptions (403)
        if ($e instanceof AuthorizationException) {
            $this->logUnauthorizedAccess($request, $e);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access Denied',
                    'message' => 'You do not have permission to access this resource.'
                ], 403);
            }

            return response()->view('errors.403', [
                'exception' => $e
            ], 403);
        }

        // Handle Authentication Exceptions (401)
        if ($e instanceof AuthenticationException) {
            $this->logUnauthorizedAccess($request, $e);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Authentication required.'
                ], 401);
            }

            return response()->view('errors.401', [
                'exception' => $e
            ], 401);
        }

        // Handle 403 HTTP Exceptions
        if ($e instanceof HttpException && $e->getStatusCode() === 403) {
            $this->logUnauthorizedAccess($request, $e);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access Denied',
                    'message' => $e->getMessage() ?: 'Forbidden.'
                ], 403);
            }

            return response()->view('errors.403', [
                'exception' => $e
            ], 403);
        }

        return parent::render($request, $e);
    }

    /**
     * Log unauthorized access attempts for security monitoring
     */
    protected function logUnauthorizedAccess($request, Throwable $exception): void
    {
        try {
            // Log to Laravel's log file
            Log::warning('Unauthorized access attempt', [
                'user_id' => auth()->id(),
                'email' => auth()->user()->email ?? 'Guest',
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'timestamp' => now(),
            ]);

            // Log to database transaction logs if user is authenticated
            if (auth()->check()) {
                Transaction_Logs::create([
                    'user_id' => auth()->id(),
                    'activity_type' => 'UNAUTHORIZED_ACCESS_ATTEMPT',
                    'activity_description' => 'Attempted to access: ' . $request->fullUrl() . ' - ' . $exception->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if logging fails - don't break the error page
            Log::error('Failed to log unauthorized access: ' . $e->getMessage());
        }
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $this->logUnauthorizedAccess($request, $exception);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Authentication required to access this resource.'
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
