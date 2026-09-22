<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Controllers\UserResourcePackController;
use App\Models\ResourcePack;
use App\Models\User;
use App\Support\Roles;

/**
 * The per-member quota on hub installs, shared by the Themes page (which shows
 * it) and {@see UserResourcePackController::install()} (which enforces it).
 */
trait LimitsResourcePackInstalls
{
    private function installLimit(): int
    {
        return (int) config('runemanager.resource_packs.user_install_limit', 5);
    }

    private function userInstallCount(User $user): int
    {
        return ResourcePack::where('installed_by_user_id', $user->id)->count();
    }

    /** The owner manages packs instance-wide, so the per-member quota doesn't apply. */
    private function canInstallMore(User $user): bool
    {
        return $user->can(Roles::MANAGE_INSTANCE)
            || $this->userInstallCount($user) < $this->installLimit();
    }
}
