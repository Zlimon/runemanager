<?php

namespace App\Services\ResourcePacks;

use App\Models\ResourcePack;
use App\Models\User;
use App\Support\Roles;

/**
 * The per-member quota on hub installs, shown on the Themes page and enforced
 * by the member install endpoint. Holders of `manage instance` manage packs
 * instance-wide, so the quota does not apply to them.
 */
class PackInstallQuota
{
    public function limit(): int
    {
        return (int) config('runemanager.resource_packs.user_install_limit', 5);
    }

    public function countFor(User $user): int
    {
        return ResourcePack::where('installed_by_user_id', $user->id)->count();
    }

    public function allows(User $user): bool
    {
        return $user->can(Roles::MANAGE_INSTANCE)
            || $this->countFor($user) < $this->limit();
    }
}
