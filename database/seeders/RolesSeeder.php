<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Demo-only roles, each holding a single management permission so the
     * gates can be checked independently of the all-powerful Owner. They are
     * not part of `Roles::sync()` — a real instance builds its own.
     *
     * @var array<string, string>
     */
    private const DEMO_ROLES = [
        'Moderator' => Roles::MANAGE_MEMBERS,
        'Event Organiser' => Roles::MANAGE_CALENDAR,
    ];

    /**
     * Create the base roles + permissions, bootstrap the instance Owner as the
     * earliest registered user, and give every other user the default role real
     * registration assigns. Idempotent — safe to re-run.
     */
    public function run(): void
    {
        Roles::sync();

        $users = User::query()->oldest('id')->get();
        if ($users->isEmpty()) {
            $this->command->warn('RolesSeeder: no users found; run UserSeeder first.');

            return;
        }

        $owner = $users->shift();
        if (! $owner->hasRole(Roles::OWNER)) {
            $owner->assignRole(Roles::OWNER);
        }

        // CreateNewUser::assignDefaultRole does this on registration; seeded
        // users skip that path, so a demo instance would otherwise be full of
        // users holding no role at all.
        foreach ($users as $user) {
            if (! $user->hasRole(Roles::USER)) {
                $user->assignRole(Roles::USER);
            }
        }

        $this->seedDemoRoles($users);
    }

    /**
     * @param  Collection<int, User>  $users  non-owner users, oldest first
     */
    private function seedDemoRoles(Collection $users): void
    {
        $candidates = $users->values();
        $index = 0;

        foreach (self::DEMO_ROLES as $name => $permission) {
            Role::findOrCreate($name, 'web')->givePermissionTo($permission);

            $user = $candidates->get($index++);
            if ($user && ! $user->hasRole($name)) {
                $user->assignRole($name);
            }
        }

        $this->command->info(sprintf(
            'RolesSeeder: %d users hold a role; demo roles: %s.',
            User::query()->whereHas('roles')->count(),
            implode(', ', array_keys(self::DEMO_ROLES)),
        ));
    }
}
