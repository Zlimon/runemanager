<?php

use App\Models\Item;
use App\Services\Items\ItemsSync;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;

/*
 * mongodb-static is normally shared (read-only in tests, see phpunit.xml);
 * this suite writes to it, so point it at a scratch database on the main
 * mongo server instead, keeping the real static dataset untouched.
 */
beforeEach(function () {
    config([
        'database.connections.mongodb-static.dsn' => config('database.connections.mongodb.dsn'),
        'database.connections.mongodb-static.database' => 'gamedata_testing',
    ]);
    DB::purge('mongodb-static');
    Item::query()->delete();
});

afterEach(fn () => Item::query()->delete());

function itemsSyncWith(array $responses): ItemsSync
{
    $client = new Client(['handler' => HandlerStack::create(new MockHandler($responses))]);

    return new ItemsSync($client);
}

/*
 * Seed through the raw collection — Eloquent inserts alias `id` to `_id`,
 * losing the string OSRS id field the sync and lookups match on.
 */
function seedItem(array $document): void
{
    (new Item)->getConnection()
        ->getDatabase()
        ->selectCollection((new Item)->getTable())
        ->insertOne($document);
}

function namesResponse(array $names): Response
{
    return new Response(200, [], json_encode($names));
}

it('inserts missing items with runelite icons and wiki metadata', function () {
    seedItem(['id' => '4151', 'name' => 'Abyssal whip']);

    $sync = itemsSyncWith([
        namesResponse(['4151' => 'Abyssal whip', '33824' => 'Stymphike']),
        new Response(200, [], json_encode([[
            'id' => 33824,
            'examine' => 'A mythical bird.',
            'members' => true,
            'value' => 100,
            'lowalch' => 40,
            'highalch' => 60,
            'limit' => 8,
        ]])),
        new Response(200, [], 'png-bytes'),
    ]);

    $result = $sync->sync();

    expect($result)->toBe(['missing' => 1, 'inserted' => 1, 'icons_missing' => 0]);

    $item = Item::lookupByOsrsId(33824);
    expect($item['name'])->toBe('Stymphike')
        ->and($item['examine'])->toBe('A mythical bird.')
        ->and($item['icon'])->toBe(base64_encode('png-bytes'))
        ->and($item['lowalch'])->toBe(40)
        ->and($item['highalch'])->toBe(60);

    expect(Item::count())->toBe(2)
        ->and(data_get(Item::lookupByOsrsId(4151), 'name'))->toBe('Abyssal whip');
});

it('does nothing when every game item is already present', function () {
    seedItem(['id' => '4151', 'name' => 'Abyssal whip']);

    $sync = itemsSyncWith([
        namesResponse(['4151' => 'Abyssal whip']),
    ]);

    expect($sync->sync())->toBe(['missing' => 0, 'inserted' => 0, 'icons_missing' => 0])
        ->and(Item::count())->toBe(1);
});

it('still inserts an item when its icon or wiki metadata is unavailable', function () {
    $sync = itemsSyncWith([
        namesResponse(['33824' => 'Stymphike']),
        new Response(503, [], 'wiki down'),
        new RequestException('no icon', new Request('GET', 'icon/33824.png')),
    ]);

    $result = $sync->sync();

    expect($result)->toBe(['missing' => 1, 'inserted' => 1, 'icons_missing' => 1]);

    $item = Item::lookupByOsrsId(33824);
    expect($item['name'])->toBe('Stymphike')
        ->and($item['icon'])->toBeNull()
        ->and($item['examine'])->toBeNull();
});

it('skips placeholder "null" names from the cache dump', function () {
    $sync = itemsSyncWith([
        namesResponse(['1' => 'null', '2' => 'Cannonball']),
        new Response(200, [], json_encode([])),
        new Response(200, [], 'png-bytes'),
    ]);

    $result = $sync->sync();

    expect($result['inserted'])->toBe(1)
        ->and(data_get(Item::lookupByOsrsId(2), 'name'))->toBe('Cannonball')
        ->and(Item::count())->toBe(1);
});

it('honours the limit option', function () {
    $sync = itemsSyncWith([
        namesResponse(['1' => 'One', '2' => 'Two', '3' => 'Three']),
        new Response(200, [], json_encode([])),
        new Response(200, [], 'png-bytes'),
    ]);

    $result = $sync->sync(limit: 1);

    expect($result)->toBe(['missing' => 1, 'inserted' => 1, 'icons_missing' => 0])
        ->and(Item::count())->toBe(1);
});

it('runs via the artisan command and reports counts', function () {
    $this->mock(ItemsSync::class, function ($mock) {
        $mock->shouldReceive('sync')->once()
            ->andReturn(['missing' => 2, 'inserted' => 2, 'icons_missing' => 1]);
    });

    $this->artisan('items:sync')
        ->expectsOutputToContain('Inserted 2 of 2 missing items (1 without an icon).')
        ->assertSuccessful();
});
