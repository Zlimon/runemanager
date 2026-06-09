<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Create the base roles + permissions (SPEC §3.4) and bootstrap the instance
     * Owner as the earliest registered user. Idempotent — safe to re-run.
     */
    public function run(): void
    {
        Roles::sync();

        $owner = User::query()->oldest('id')->first();
        if ($owner && ! $owner->hasRole(Roles::OWNER)) {
            $owner->assignRole(Roles::OWNER);
        }
    }
}
