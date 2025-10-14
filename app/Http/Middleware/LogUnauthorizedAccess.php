<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction_Logs;
use Symfony\Component\HttpFoundation\Response;

class LogUnauthorizedAccess
{
    /**
     * Handle an incoming request and log any suspicious access patterns.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log if response is 401 or 403
        if (in_array($response->getStatusCode(), [401, 403])) {
            $this->logAccessDenied($request, $response->getStatusCode());
        }

        return $response;
    }

    /**
     * Log the unauthorized access attempt
     */
    protected function logAccessDenied(Request $request, int $statusCode): void
    {
        try {
            $activityType = $statusCode === 401 ? 'UNAUTHENTICATED_ACCESS' : 'UNAUTHORIZED_ACCESS';
            $description = $statusCode === 401
                ? 'Attempted to access protected route without authentication'
                : 'Attempted to access forbidden resource';

            // Log to file
            Log::warning("Access Denied ({$statusCode})", [
                'user_id' => auth()->id(),
                'email' => auth()->user()->email ?? 'Guest',
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'referrer' => $request->header('referer'),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);

            // Log to database if user is authenticated
            if (auth()->check()) {
                Transaction_Logs::create([
                    'user_id' => auth()->id(),
                    'activity_type' => $activityType,
                    'activity_description' => $description . ': ' . $request->fullUrl(),
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the application
            Log::error('Failed to log unauthorized access in middleware: ' . $e->getMessage());
        }
    }
}
