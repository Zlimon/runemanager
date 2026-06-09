<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

/**
 * Create a user that passes the `admin` gate. Ensures the base roles exist
 * (RefreshDatabase wipes them) and assigns the given role.
 */
function adminUser(string $role = 'admin'): User
{
    foreach (['owner', 'admin', 'member'] as $name) {
        Role::findOrCreate($name, 'web');
    }

    return tap(User::factory()->withPersonalTeam()->create())->assignRole($role);
}
