<?php

namespace App\Services\Users;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Validation\ValidationException;

/**
 * Make a user an Owner or a plain User. The two base roles are exclusive, so
 * the other one is removed; any role outside the base pair is left alone.
 */
class UpdateUserRole
{
    public function __construct(private LastOwnerGuard $guard) {}

    /**
     * @throws ValidationException when this demotes the last active owner
     */
    public function run(User $user, string $role): void
    {
        Roles::sync();

        if ($role === Roles::OWNER) {
            $this->swap($user, Roles::USER, Roles::OWNER);

            return;
        }

        $this->guard->run($user, 'The last active owner cannot be demoted.', fn () => $this->swap($user, Roles::OWNER, $role));
    }

    private function swap(User $user, string $from, string $to): void
    {
        if ($user->hasRole($from)) {
            $user->removeRole($from);
        }

        $user->assignRole($to);
    }
}
