<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\Users\UpdateUserRole;
use Illuminate\Http\RedirectResponse;

class UserRoleController extends Controller
{
    public function update(UpdateUserRoleRequest $request, User $user, UpdateUserRole $updater): RedirectResponse
    {
        $role = $request->validated('role');

        abort_if($user->is($request->user()), 403, 'You cannot change your own role.');

        $updater->run($user, $role);

        return back()->banner("{$user->name} is now {$role}.");
    }
}
