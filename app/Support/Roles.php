<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * SPEC §3.4 — the site's base access-control model, independent of instance
 * mode. There are two base roles: `Owner` (holds every permission) and `User`
 * (the default for new accounts, no management permissions). Clan/group-derived
 * elevation is layered on top of this later.
 */
class Roles
{
    public const OWNER = 'Owner';

    public const USER = 'User';

    public const MANAGE_INSTANCE = 'manage instance';

    public const MANAGE_MEMBERS = 'manage members';

    public const MANAGE_ANNOUNCEMENTS = 'manage announcements';

    public const MANAGE_CALENDAR = 'manage calendar';

    /**
     * Every management permission. The Owner holds all of them.
     *
     * @return list<string>
     */
    public static function permissions(): array
    {
        return [
            self::MANAGE_INSTANCE,
            self::MANAGE_MEMBERS,
            self::MANAGE_ANNOUNCEMENTS,
            self::MANAGE_CALENDAR,
        ];
    }

    /**
     * Ensure the base roles + permissions exist and that Owner holds them all.
     * Idempotent — safe to call from the seeder and on registration.
     */
    public static function sync(): void
    {
        $permissions = array_map(
            fn (string $name): Permission => Permission::findOrCreate($name, 'web'),
            self::permissions(),
        );

        Role::findOrCreate(self::OWNER, 'web')->syncPermissions($permissions);
        Role::findOrCreate(self::USER, 'web');
    }
}
