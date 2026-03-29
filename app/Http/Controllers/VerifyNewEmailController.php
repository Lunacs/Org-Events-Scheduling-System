<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class VerifyNewEmailController extends Controller
{
    /**
     * Verify the new email address and swap it in.
     */
    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        // Ensure the hash matches the pending email
        if (!hash_equals($hash, sha1($user->pending_email ?? ''))) {
            abort(403, 'Invalid verification link.');
        }

        $user->confirmEmailChange();

        // Redirect to the user's profile page with a success message
        return redirect()->route('profile')
            ->with('status', 'Your email address has been updated successfully!');
    }

    /**
     * Cancel the pending email change.
     */
    public function cancel(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        // Ensure the hash matches the pending email
        if (!hash_equals($hash, sha1($user->pending_email ?? ''))) {
            abort(403, 'Invalid cancellation link.');
        }

        $user->cancelEmailChange();

        return redirect()->route('profile')
            ->with('status', 'Email change has been cancelled.');
    }
}
