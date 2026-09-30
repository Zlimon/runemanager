<?php

namespace App\Services\Users;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Run a change that would take a user out of the active owners (suspend,
 * demote, delete) only if another active owner remains.
 *
 * The check and the change share one transaction that first locks every
 * owner's row, so two owners acting on each other at the same moment are
 * serialised: the second one sees the first one's change and is refused,
 * rather than both passing the check and leaving the instance ownerless.
 * (SQLite, which the tests run on, ignores the lock and serialises writes
 * anyway.)
 */
class LastOwnerGuard
{
    /**
     * @template T
     *
     * @param  Closure(): T  $change
     * @return T
     *
     * @throws ValidationException
     */
    public function run(User $user, string $message, Closure $change, string $errorKey = 'user'): mixed
    {
        return DB::transaction(function () use ($user, $message, $change, $errorKey) {
            User::query()->owners()->lockForUpdate()->pluck('id');

            if ($user->isLastActiveOwner()) {
                throw ValidationException::withMessages([$errorKey => $message]);
            }

            return $change();
        });
    }
}
