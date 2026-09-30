<?php

namespace App\Services\Users;

use App\Models\User;

/**
 * Lift a suspension. The revoked tokens stay revoked: the plugin signs in
 * again with the user's credentials.
 */
class UnsuspendUser
{
    public function run(User $user): void
    {
        $user->forceFill([
            'suspended_at' => null,
            'suspension_reason' => null,
            'suspended_by_user_id' => null,
        ])->save();
    }
}
