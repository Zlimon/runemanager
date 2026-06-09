<?php

use App\Models\User;
use App\Support\Roles;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

/**
 * Create a user with the given base role (defaults to Owner, which holds every
 * management permission). Ensures the roles/permissions exist first, since
 * RefreshDatabase wipes them.
 */
function adminUser(string $role = Roles::OWNER): User
{
    Roles::sync();

    return tap(User::factory()->withPersonalTeam()->create())->assignRole($role);
}
