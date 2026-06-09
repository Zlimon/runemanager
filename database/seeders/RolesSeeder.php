<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Create the base roles (SPEC §3.4) and bootstrap the instance owner as the
     * earliest registered user. Idempotent — safe to re-run.
     */
    public function run(): void
    {
        foreach (['owner', 'admin', 'member'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $owner = User::query()->oldest('id')->first();
        if ($owner && ! $owner->hasRole('owner')) {
            $owner->assignRole('owner');
        }
    }
}
