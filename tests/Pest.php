<?php

use App\Models\Bank;
use App\Models\CollectionLog;
use App\Models\GroupBank;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\PluginRequest;
use App\Models\Quest;
use App\Models\ResourcePack;
use App\Models\User;
use App\Models\WomDailySnapshot;
use App\Services\Screenshots\ScreenshotStorage;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

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
| Extend this list if a new model adds `#[Connection('mongodb')]`.
*/

pest()->beforeEach(function () {
    try {
        foreach ([Bank::class, CollectionLog::class, GroupBank::class, Inventory::class, Loot::class, LootingBag::class, PluginRequest::class, Quest::class, WomDailySnapshot::class] as $model) {
            $model::query()->delete();
        }
    } catch (Throwable) {
        // No Mongo available in this test context (pure unit tests).
    }
})->in('Feature', 'Unit');

// Screenshots live on the private `local` disk and 3D avatars on the `public`
// one, and an instance reset wipes both whole. Faking them for every feature
// test keeps a test that resets, seeds or deletes from ever touching the
// development screenshots and avatars.
pest()->beforeEach(function () {
    Storage::fake(ScreenshotStorage::DISK);
    Storage::fake('public');
})->in('Feature');

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

/**
 * A dark-drawn resource pack row, shared by the dark-mode and appearance tests.
 */
function darkPack(string $name = 'pack-darkish'): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => $name,
        'alias' => 'Darkish',
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => true,
    ]);
}

/**
 * A plain member with no team or roles, for the resource-pack tests. Distinct
 * from adminUser() above, which exists to hold management permissions.
 */
function freshPackUser(string $email = 'pack@test.local'): User
{
    return User::query()->forceCreate([
        'name' => 'Pack Tester',
        'email' => $email,
        'email_verified_at' => now(),
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

/**
 * An installed pack row. Pass `installed_by_user_id` for a member's own
 * install, or `version: 'pending'` for one whose files are still being fetched.
 */
function makePack(string $name, array $overrides = []): ResourcePack
{
    return ResourcePack::query()->forceCreate(array_merge([
        'name' => $name,
        'alias' => ucfirst($name),
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => false,
    ], $overrides));
}

/**
 * A trimmed, real Wise Old Man API response from tests/Fixtures/WiseOldMan.
 *
 * @return array<mixed>
 */
function womFixture(string $name): array
{
    return json_decode(file_get_contents(__DIR__."/Fixtures/WiseOldMan/{$name}.json"), true);
}
