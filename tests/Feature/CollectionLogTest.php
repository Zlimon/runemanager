<?php

use App\Models\Account;
use App\Models\CollectionLog;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use MongoDB\Driver\Exception\BulkWriteException;

uses(RefreshDatabase::class);

beforeEach(function () {
    // RefreshDatabase only resets the SQL DB; the Mongo collection persists across tests.
    CollectionLog::query()->delete();
});

function makeAccountForLog(string $username = 'Zlimon'): Account
{
    $user = User::query()->forceCreate([
        'name' => 'Test',
        'email' => $username.'@test.local',
        'password' => bcrypt('password'),
        'icon_id' => 0,
    ]);

    return Account::query()->forceCreate([
        'user_id' => $user->id,
        'account_type' => 'normal',
        'username' => $username,
        'rank' => 0,
        'level' => 0,
        'xp' => 0,
    ]);
}

it('round-trips a collection log document (TempleOSRS shape)', function () {
    $account = makeAccountForLog();

    CollectionLog::create([
        'account_id' => $account->id,
        'obtained' => 2,
        'total' => 1701,
        'categories_finished' => 1,
        'categories_available' => 122,
        'items' => [
            'abyssal_sire' => [
                ['id' => 13262, 'count' => 1, 'date' => '2026-06-01 12:00:00'],
                ['id' => 13265, 'count' => 2, 'date' => '2026-06-01 12:00:00'],
            ],
        ],
        'fetched_at' => now(),
    ]);

    $fetched = CollectionLog::where('account_id', $account->id)->first();

    expect($fetched)->not->toBeNull();
    expect($fetched->obtained)->toBe(2);
    expect($fetched->total)->toBe(1701);
    expect($fetched->items['abyssal_sire'])->toHaveCount(2);
    expect($fetched->items['abyssal_sire'][0]['id'])->toBe(13262);
});

it('enforces one document per account via the unique index', function () {
    $account = makeAccountForLog();

    CollectionLog::create(['account_id' => $account->id, 'items' => []]);

    expect(fn () => CollectionLog::create(['account_id' => $account->id, 'items' => []]))
        ->toThrow(BulkWriteException::class);
});

it('Account::collectionLog accessor returns null when no doc exists', function () {
    $account = makeAccountForLog();

    expect($account->collectionLog)->toBeNull();
});

it('Account::collectionLog accessor returns the doc when it exists', function () {
    $account = makeAccountForLog();

    CollectionLog::create([
        'account_id' => $account->id,
        'obtained' => 1,
        'total' => 1701,
        'items' => ['vorkath' => [['id' => 21992, 'count' => 1]]],
    ]);

    expect($account->collectionLog)
        ->toBeInstanceOf(CollectionLog::class)
        ->and($account->collectionLog->items['vorkath'][0]['id'])->toBe(21992);
});

it('overlays obtained items on the full category structure (obtained + missing)', function () {
    $account = makeAccountForLog('LogViewer');

    // A real item from the static collection so the lookup hydrates its name.
    // Items store the OSRS id in the string `id` field; matching on Mongo's `_id`
    // (as the page once did) resolves nothing — this also guards that regression.
    $item = (new Item)->getConnection()->getDatabase()
        ->selectCollection((new Item)->getTable())
        ->findOne([], ['projection' => ['id' => 1, 'name' => 1, '_id' => 0]]);
    $itemId = (int) $item['id'];
    $missingId = 999999; // not obtained, not in the static collection

    // Seed the (normally fetched + week-cached) TempleOSRS structure: the category
    // has two items; the player has only one. Missing one should still render.
    Cache::put('templeosrs:collection-log-structure', [
        'bosses' => ['abyssal_sire' => [$itemId, $missingId]],
    ]);

    CollectionLog::create([
        'account_id' => $account->id,
        'obtained' => 1,
        'total' => 1701,
        'items' => ['abyssal_sire' => [['id' => $itemId, 'count' => 1, 'date' => '2026-06-01 12:00:00']]],
        'fetched_at' => now(),
    ]);

    // Match the asset version so the Inertia middleware doesn't 409 the partial.
    $version = file_exists($m = public_path('build/manifest.json')) ? hash_file('xxh128', $m) : '';

    $response = $this->actingAs($account->user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $version,
            'X-Inertia-Partial-Data' => 'collectionLog',
            'X-Inertia-Partial-Component' => 'Accounts/Show',
        ])
        ->get(route('accounts.show', $account))
        ->assertOk();

    $active = $response->json('props.collectionLog.activeCollection');

    expect($active['group'])->toBe('bosses')
        ->and($active['total'])->toBe(2)
        ->and($active['obtained'])->toBe(1)
        ->and($active['items'])->toHaveCount(2);

    $obtained = collect($active['items'])->firstWhere('id', $itemId);
    expect($obtained['obtained'])->toBeTrue()
        ->and($obtained['item']['name'])->toBe($item['name']);

    $missing = collect($active['items'])->firstWhere('id', $missingId);
    expect($missing['obtained'])->toBeFalse();
});
