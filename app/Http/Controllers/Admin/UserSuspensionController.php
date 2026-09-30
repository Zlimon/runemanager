<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuspendUserRequest;
use App\Models\User;
use App\Services\Users\SuspendUser;
use App\Services\Users\UnsuspendUser;
use Illuminate\Http\RedirectResponse;

class UserSuspensionController extends Controller
{
    public function store(SuspendUserRequest $request, User $user, SuspendUser $suspender): RedirectResponse
    {
        abort_if($user->is($request->user()), 403, 'You cannot suspend yourself.');

        $suspender->run($user, $request->user(), $request->validated('reason'));

        return back()->banner("Suspended {$user->name}.");
    }

    public function destroy(User $user, UnsuspendUser $unsuspender): RedirectResponse
    {
        $unsuspender->run($user);

        return back()->banner("Lifted {$user->name}'s suspension.");
    }
}
