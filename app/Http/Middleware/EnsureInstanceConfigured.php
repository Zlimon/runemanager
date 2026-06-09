<?php

namespace App\Http\Middleware;

use App\Support\Instance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SPEC §12 — until the owner completes first-time setup (choosing the instance
 * mode), authenticated users are funnelled to the setup page. The first
 * registered user is the owner, so this forces them through setup before the
 * rest of the site is reachable.
 */
class EnsureInstanceConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Instance::isConfigured()
            && $request->user() !== null
            && ! $request->routeIs('admin.settings', 'admin.settings.update', 'logout')) {
            return redirect()->route('admin.settings');
        }

        return $next($request);
    }
}
