<?php

namespace App\Http\Middleware;

use App\Support\Instance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * SPEC §12 — until the owner completes first-time setup (choosing the instance
 * mode), the site is closed: the owner is funnelled to the setup page and
 * everyone else (guests + non-admins) gets a "setup in progress" screen. The
 * auth routes stay open so the owner can register/log in to do the setup.
 */
class EnsureInstanceConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Instance::isConfigured() || $this->isExempt($request)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user !== null && Gate::forUser($user)->allows('admin')) {
            return redirect()->route('admin.settings');
        }

        return Inertia::render('SetupInProgress', [
            'canRegister' => Route::has('register'),
        ])->toResponse($request);
    }

    /**
     * Routes that must stay reachable during setup: authentication (so the owner
     * can sign in), the setup page itself, and per-user preferences.
     */
    private function isExempt(Request $request): bool
    {
        return $request->routeIs(
            'login', 'login.store', 'logout',
            'register', 'register.store',
            'password.*', 'two-factor.*',
            'admin.settings', 'admin.settings.update',
            'user.*',
        );
    }
}
