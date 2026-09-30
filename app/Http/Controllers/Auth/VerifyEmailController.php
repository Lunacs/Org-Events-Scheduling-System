<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TransactionLogService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->getRedirectRoute($request->user(), true);
        }

        if ($request->user()->markEmailAsVerified()) {
            // Log email verification
            TransactionLogService::logAuthEvent('email_verified', $request->user());

            event(new Verified($request->user()));
        }

        return $this->getRedirectRoute($request->user(), true);
    }

    /**
     * Get the appropriate redirect route based on user role.
     */
    public function getRedirectRoute($user, $verified = false): RedirectResponse
    {
        $routeName = $user->getDashboardRoute();

        $redirect = redirect()->intended(route($routeName, [], false));

        if ($verified) {
            $redirect = $redirect->with('verified', true);
        }

        return $redirect;
    }
}
