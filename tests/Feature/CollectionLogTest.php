<?php

use App\Models\Account;
use App\Models\CollectionLog;
use App\Models\Item;
use App\Models\User;
use App\Services\CollectionLog\CollectionLogView;
use App\Services\TempleOsrs\TempleOsrsClient;
use Illuminate\Support\Facades\Cache;
use MongoDB\Driver\Exception\BulkWriteException;

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
            'X-Inertia-Partial-Component' => 'Accounts/Collection/Show',
        ])
        ->get(route('accounts.collection', $account))
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

it('falls back to the obtained-only categories when the structure is unavailable', function () {
    $account = makeAccountForLog('Offline');

    $this->mock(TempleOsrsClient::class)
        ->shouldReceive('collectionLogStructure')->andReturn(null);

    CollectionLog::create([
        'account_id' => $account->id,
        'obtained' => 3,
        'total' => 1701,
        'items' => [
            'vorkath' => [['id' => 999991, 'count' => 1]],
            'abyssal_sire' => [['id' => 999992, 'count' => 2], ['id' => 999993, 'count' => 1]],
        ],
        'fetched_at' => now(),
    ]);

    $view = app(CollectionLogView::class)->build($account, 'vorkath');

    expect($view['categories'])->toBe([
        ['slug' => 'abyssal_sire', 'name' => 'Abyssal Sire', 'group' => 'other', 'obtained' => 2, 'total' => 2],
        ['slug' => 'vorkath', 'name' => 'Vorkath', 'group' => 'other', 'obtained' => 1, 'total' => 1],
    ])
        ->and($view['activeSlug'])->toBe('vorkath')
        ->and($view['activeCollection']['items'])->toHaveCount(1)
        ->and($view['activeCollection']['items'][0]['obtained'])->toBeTrue()
        ->and($view['obtained'])->toBe(3)
        ->and($view['total'])->toBe(1701);

    expect(app(CollectionLogView::class)->build($account, 'unknown')['activeSlug'])->toBe('abyssal_sire');
});

it('builds no collection log view for an account without a synced log', function () {
    expect(app(CollectionLogView::class)->build(makeAccountForLog('NoLog'), null))->toBeNull();
});
