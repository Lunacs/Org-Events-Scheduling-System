<?php

namespace App\Livewire\Actions;

use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        $user = Auth::user();

        // Log logout event before destroying session
        if ($user) {
            TransactionLogService::logAuthEvent('logout', $user);
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
