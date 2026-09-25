<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps customers in the portal and staff/professionals in the admin panel,
 * and signs out accounts that have been deactivated.
 *
 * Usage: ->middleware('user.type:customer') or ('user.type:backoffice')
 */
class EnsureUserType
{
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $user = $request->user();

        if (! $user->isActive()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['login' => 'Your account is inactive. Please contact support.']);
        }

        $allowed = match ($type) {
            'customer' => $user->isCustomer() && $user->customer !== null,
            'backoffice' => $user->isBackoffice(),
            default => false,
        };

        if (! $allowed) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
