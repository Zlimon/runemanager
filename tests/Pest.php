<?php

use App\Models\Bank;
use App\Models\CollectionLog;
use App\Models\GroupBank;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Npc;
use App\Models\Quest;
use App\Models\User;
use App\Support\Roles;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Mongo isolation
|--------------------------------------------------------------------------
|
| RefreshDatabase only wraps the SQL connection in a transaction — writes to
| the `mongodb` connection (every model below) are never rolled back, so
| they'd otherwise accumulate across tests indefinitely. That was masked
| under Postgres (whose sequences never reuse an id within a run, so a
| leftover doc's account_id essentially never matches a fresh test's
| account again), but SQLite reuses primary keys after a rolled-back
| transaction — a "fresh" account can and does land on the same id an
| earlier test used, picking up that test's leftover Mongo data.
|
| Deleting each collection's documents (rather than dropping the whole
| testing database) keeps this cheap and avoids racing the unique-index
| migrations that run against these same collections.
|
| Extend this list if a new model adds `protected $connection = 'mongodb'`.
*/

pest()->beforeEach(function () {
    try {
        foreach ([Bank::class, CollectionLog::class, GroupBank::class, Inventory::class, Loot::class, LootingBag::class, Npc::class, Quest::class] as $model) {
            $model::query()->delete();
        }
    } catch (Throwable) {
        // No Mongo available in this test context (pure unit tests).
    }
})->in('Feature', 'Unit');

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
