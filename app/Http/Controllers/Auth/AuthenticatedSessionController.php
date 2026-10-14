<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        $redirectRoute = match ($user?->portal()) {
            'superadmin' => 'superadmin.login',
            'osa' => 'admin.login',
            'gso' => 'gso.login',
            'student-org' => 'student-org.login',
            default => 'login',
        };

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route($redirectRoute);
    }
}
