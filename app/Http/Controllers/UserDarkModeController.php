<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Persist the authenticated user's light/dark preference.
 *
 * This is only consulted when no resource pack is in effect — a pack (user or
 * instance-global) carries its own dark_mode flag that overrides this in
 * {@see HandleInertiaRequests::share()}.
 */
class UserDarkModeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dark_mode' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $user->dark_mode = $validated['dark_mode'];
        $user->save();

        return back();
    }
}
