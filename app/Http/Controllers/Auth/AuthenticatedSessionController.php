<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Log logout event before destroying session
        if ($user) {
            TransactionLogService::logAuthEvent('logout', $user);
        }

        $redirectRoute = match ($user->role_id ?? null) {
            User::getRoleId('superadmin') => 'superadmin.login',
            User::getRoleId('osa') => 'admin.login',
            User::getRoleId('gso') => 'gso.login',
            User::getRoleId('student-org') => 'student-org.login',
            default => 'admin.login',
        };

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route($redirectRoute);
    }
}
