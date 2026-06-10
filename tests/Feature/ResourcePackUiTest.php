<?php

use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Services\ResourcePacks\ResourcePackHub;
use App\Support\Roles;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

function uiPack(string $name): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => $name,
        'alias' => ucfirst($name),
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => false,
    ]);
}

it('ensures the bundled Default Vanilla pack exists (idempotent)', function () {
    $installer = app(InstallResourcePack::class);

    $pack = $installer->ensureVanilla();

    expect($pack)->not->toBeNull();
    expect($pack->name)->toBe('sample-vanilla');
    expect($pack->alias)->toBe('Default Vanilla');

    $installer->ensureVanilla();
    expect(ResourcePack::where('name', 'sample-vanilla')->count())->toBe(1);
});

it('lists Default Vanilla first on the appearance page', function () {
    uiPack('pack-zebra');
    $user = User::factory()->withPersonalTeam()->create();

    $this->actingAs($user)
        ->get(route('themes.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('packs.0.name', 'sample-vanilla')
            ->where('packs.0.alias', 'Default Vanilla'),
        );
});

it('renders the user appearance page with installed packs', function () {
    $pack = uiPack('pack-rs3');
    $user = User::factory()->withPersonalTeam()->create(['resource_pack_id' => $pack->id]);

    $this->actingAs($user)
        ->get(route('themes.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Themes/Index')
            ->where('selectedId', $pack->id)
            // Default Vanilla is auto-ensured and pinned first; pack-rs3 follows.
            ->has('packs', 2)
            ->where('packs.0.name', 'sample-vanilla')
            ->where('packs.1.name', 'pack-rs3')
            ->has('packs.1.icon_url'),
        );
});

it('sets the personal override via an inertia request and redirects back', function () {
    $pack = uiPack('pack-pinklite');
    $user = User::factory()->withPersonalTeam()->create();

    $this->actingAs($user)
        ->put(route('user.resource-pack.update'), ['resource_pack_id' => $pack->id], ['X-Inertia' => 'true'])
        ->assertRedirect();

    expect($user->fresh()->resource_pack_id)->toBe($pack->id);
});

it('lists hub packs and flags installed ones', function () {
    uiPack('pack-rs3');

    $hub = new ResourcePackHub(new Client(['handler' => HandlerStack::create(new MockHandler([
        new Response(200, [], json_encode([
            ['name' => 'pack-rs3'],
            ['name' => 'pack-win95ish'],
            ['name' => 'github-actions'], // non-pack branch, filtered out
        ])),
    ]))]));

    $available = $hub->available();

    expect($available)->toHaveCount(2);
    expect(collect($available)->firstWhere('name', 'pack-rs3')['installed'])->toBeTrue();
    expect(collect($available)->firstWhere('name', 'pack-win95ish')['installed'])->toBeFalse();
    expect($available[0]['icon_url'])->toContain('raw.githubusercontent.com');
});

it('caches the hub branch list', function () {
    Cache::flush();
    $hub = new ResourcePackHub(new Client(['handler' => HandlerStack::create(new MockHandler([
        new Response(200, [], json_encode([['name' => 'pack-rs3']])),
        // No second response queued — a second fetch would throw, proving the cache.
    ]))]));

    expect($hub->branches())->toBe(['pack-rs3']);
    expect($hub->branches())->toBe(['pack-rs3']);
});

it('queues an install from the hub', function () {
    Queue::fake();

    $this->actingAs(adminUser())
        ->post(route('admin.packs.install'), ['name' => 'pack-win95ish'])
        ->assertRedirect();

    Queue::assertPushed(FetchResourcePackJob::class,
        fn (FetchResourcePackJob $job) => $job->packName === 'pack-win95ish');
});

it('validates the hub install name', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.packs.install'), ['name' => 'evil/../path'])
        ->assertSessionHasErrors('name');
});

it('forbids non-admins from the hub', function () {
    $user = adminUser(Roles::USER);

    $this->actingAs($user)->get(route('admin.packs'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.packs.install'), ['name' => 'pack-rs3'])->assertForbidden();
});
