<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuse a suspended user who is still signed in: a website session that
 * outlived the suspension (any session driver but `database`, whose rows
 * SuspendUser deletes), or a token issued before it.
 *
 * It is appended to the `web` and `api` middleware groups (bootstrap/app.php),
 * so it covers every route in them, public pages and Jetstream's vendor routes
 * included. Where it lands relative to a route's own `auth` middleware is up to
 * Laravel's middleware priority sort (in the api group today it runs after
 * `auth:sanctum` and before `plugin.account`), so it does not rely on either
 * order: it resolves the user itself through the guard given as its parameter.
 * Guards cache the resolved user, so this costs no second lookup.
 */
class EnsureUserIsNotSuspended
{
    public const MESSAGE = 'Your account has been suspended.';

    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        $user = $request->user($guard);

        if ($user === null || ! $user->isSuspended()) {
            return $next($request);
        }

        // A web session (or Sanctum's stateful SPA auth, which rides on it).
        if ($request->hasSession() && Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => self::MESSAGE], 403);
        }

        return redirect()->route('login')->with('status', self::MESSAGE);
    }
}
