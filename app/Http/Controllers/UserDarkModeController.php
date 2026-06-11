<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HandleInertiaRequests;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Persist a viewer's light/dark preference.
 *
 * Logged-in users store an explicit boolean on their account; logged-out
 * visitors get a long-lived cookie so the choice survives navigation and is
 * applied server-side on first paint. An unset preference falls through to the
 * instance default and then the resource pack flag
 * (see {@see Instance::resolveDarkMode()} / {@see HandleInertiaRequests::share()}).
 */
class UserDarkModeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dark_mode' => ['required', 'boolean'],
        ]);

        $dark = (bool) $validated['dark_mode'];
        $user = $request->user();

        if ($user !== null) {
            $user->dark_mode = $dark;
            $user->save();

            return back();
        }

        return back()->withCookie(
            cookie(Instance::DARK_MODE_COOKIE, $dark ? '1' : '0', 60 * 24 * 365),
        );
    }
}
