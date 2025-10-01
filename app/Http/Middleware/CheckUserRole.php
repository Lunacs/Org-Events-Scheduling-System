<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has the required role
        switch ($role) {
            case 'superadmin':
                if (!$user->isSuperAdmin()) {
                    Auth::guard()->logout();
                    return redirect()->route('superadmin.login')->withErrors([
                        'email' => 'You are not authorized to access this area.'
                    ]);
                }
                break;
            case 'osa':
                if (!$user->isOSA()) {
                    Auth::guard()->logout();
                    return redirect()->route('admin.login')->withErrors([
                        'email' => 'You are not authorized to access this area.'
                    ]);
                }
                break;
            case 'student_org':
                if (!$user->isStudentOrg()) {
                    Auth::guard()->logout();
                    return redirect()->route('student-org.login')->withErrors([
                        'email' => 'You are not authorized to access this area.'
                    ]);
                }
                break;
            default:
                return redirect()->route('login');
        }

        return $next($request);
    }
}
