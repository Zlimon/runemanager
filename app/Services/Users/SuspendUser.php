<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Suspend a user: record who did it and why, revoke every plugin token and end
 * every website session they hold, so the suspension takes effect at once
 * rather than when a token or session next expires.
 *
 * Sessions can only be deleted up front with the `database` session driver
 * (production). With any other driver EnsureUserIsNotSuspended logs the user
 * out on their next request instead.
 */
class SuspendUser
{
    public function __construct(private LastOwnerGuard $guard) {}

    /**
     * @throws ValidationException when the user is the last active owner
     */
    public function run(User $user, User $suspendedBy, string $reason): void
    {
        $this->guard->run($user, 'The last active owner cannot be suspended.', function () use ($user, $suspendedBy, $reason) {
            $user->forceFill([
                'suspended_at' => now(),
                'suspension_reason' => $reason,
                'suspended_by_user_id' => $suspendedBy->id,
            ])->save();

            $user->tokens()->delete();
        });

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }
    }
}
