<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserTokenController extends Controller
{
    /**
     * Revoke one of the user's API tokens, or all of them when no token is
     * named. The plugin has to sign in again afterwards.
     */
    public function destroy(User $user, ?int $token = null): RedirectResponse
    {
        if ($token === null) {
            $user->tokens()->delete();

            return back()->banner("Revoked every token of {$user->name}.");
        }

        $user->tokens()->whereKey($token)->firstOrFail()->delete();

        return back()->banner('Token revoked.');
    }
}
