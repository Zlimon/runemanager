<?php

use App\Models\ResourcePack;
use App\Models\User;
use App\Services\ResourcePacks\ResourcePackHub;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;

/**
 * A hub whose GitHub answers with the given responses, in order. The branch
 * list is seeded straight into the cache so each test queues only the
 * pack.properties responses it is actually about — and an unqueued request
 * throws, which is what proves a second lookup was served from cache.
 *
 * @param  list<Response>  $responses
 */
function propertiesHub(array $responses, array $branches = ['pack-example']): ResourcePackHub
{
    Cache::flush();
    Cache::put('resource-pack-hub-branches', $branches, now()->addHour());

    return new ResourcePackHub(new Client(['handler' => HandlerStack::create(new MockHandler($responses))]));
}

it('describes a hub pack from its pack.properties', function () {
    $hub = propertiesHub([
        new Response(200, [], "displayName=Example\nauthor=Someone\ncompatibleVersion=1.6.0\ntags=Dark, HD,Modern\n"),
    ]);

    expect($hub->properties('pack-example'))->toBe([
        'author' => 'Someone',
        'version' => '1.6.0',
        'tags' => ['dark', 'hd', 'modern'],
    ]);
});

it('serves a pack it has already described from the cache', function () {
    $hub = propertiesHub([
        new Response(200, [], "author=Someone\n"),
        // Nothing else is queued: a second fetch would throw.
    ]);

    $hub->properties('pack-example');

    expect($hub->properties('pack-example')['author'])->toBe('Someone');
});

it('refuses a name the hub does not list, without asking GitHub', function () {
    // No responses queued, so any request at all would throw.
    $hub = propertiesHub([]);

    expect($hub->properties('pack-not-on-the-hub'))->toBeNull();
});

it('caches a missing pack.properties so it is not refetched on every dialog', function () {
    $hub = propertiesHub([
        new Response(404),
        // Nothing else is queued: a retry would throw.
    ]);

    expect($hub->properties('pack-example'))->toBeNull()
        ->and($hub->properties('pack-example'))->toBeNull();
});

it('does not cache a network failure, since it says nothing about the pack', function () {
    $hub = propertiesHub([
        new ConnectException('down', new Request('GET', 'x')),
        new Response(200, [], "author=Someone\n"),
    ]);

    expect($hub->properties('pack-example'))->toBeNull()
        ->and($hub->properties('pack-example')['author'])->toBe('Someone');
});

it('serves hub pack details to a signed-in member', function () {
    app()->instance(ResourcePackHub::class, propertiesHub([
        new Response(200, [], "author=Someone\ncompatibleVersion=2.0\ntags=cute\n"),
    ]));

    $this->actingAs(User::factory()->create())
        ->getJson(route('user.resource-pack.hub', ['name' => 'pack-example']))
        ->assertOk()
        ->assertExactJson(['author' => 'Someone', 'version' => '2.0', 'tags' => ['cute']]);
});

it('404s for a pack the hub does not list', function () {
    app()->instance(ResourcePackHub::class, propertiesHub([]));

    $this->actingAs(User::factory()->create())
        ->getJson(route('user.resource-pack.hub', ['name' => 'pack-nope']))
        ->assertNotFound();
});

it('rejects a name that is not a pack branch', function () {
    $this->actingAs(User::factory()->create())
        ->getJson(route('user.resource-pack.hub', ['name' => '../../etc/passwd']))
        ->assertUnprocessable();
});

it('keeps hub pack details behind sign-in', function () {
    $this->getJson(route('user.resource-pack.hub', ['name' => 'pack-example']))
        ->assertUnauthorized();
});

it('reports how many members have chosen each pack', function () {
    $pack = ResourcePack::query()->forceCreate([
        'name' => 'pack-example', 'alias' => 'Example', 'version' => '1.0.0',
        'author' => 'Someone', 'url' => '', 'tags' => '', 'dark_mode' => false,
    ]);
    User::factory()->count(3)->create(['resource_pack_id' => $pack->id]);

    $entry = collect(ResourcePack::pickerList())->firstWhere('name', 'pack-example');

    expect($entry['users_count'])->toBe(3);
});
