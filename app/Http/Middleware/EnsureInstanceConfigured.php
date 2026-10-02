<?php

namespace App\Http\Middleware;

use App\Support\Instance;
use App\Support\Roles;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Until the owner completes first-time setup (choosing the instance
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

        if ($user !== null && $user->can(Roles::MANAGE_INSTANCE)) {
            return redirect()->route('admin.settings');
        }

        return Inertia::render('SetupInProgress/Index', [
            'canRegister' => Route::has('register'),
        ])->toResponse($request);
    }

    /**
     * Routes that must stay reachable during setup: authentication (so the owner
     * can sign in), the setup page itself, per-user preferences, and the item
     * picker the register page uses.
     */
    private function isExempt(Request $request): bool
    {
        return $request->routeIs(
            'login', 'login.store', 'logout',
            'register', 'register.store',
            'password.*', 'two-factor.*',
            'admin.settings', 'admin.settings.update',
            'user.*',
            'items.search', 'items.random',
        );
    }
}
