<?php

namespace App\Http\Middleware;

use App\Models\Roles;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Allow the request only when the user's role works in the given portal.
     *
     * The `role:` parameter names a portal (`superadmin`, `osa`, `gso`,
     * `student-org` or `viewer`); custom roles belong to their base portal.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $knownPortals = [...Roles::BUILT_IN_ROLE_NAMES, Roles::VIEWER_PORTAL];

        if (! in_array($role, $knownPortals, true)) {
            return redirect()->route('login')->withErrors([
                'email' => 'You are not authorized to access this area.',
            ]);
        }

        if (! $user->inPortal($role)) {
            return redirect()->route($user->getDashboardRoute())->withErrors([
                'email' => 'You are not authorized to access this area.',
            ]);
        }

        return $next($request);
    }
}
