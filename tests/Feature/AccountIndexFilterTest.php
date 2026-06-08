<?php

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\Loot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
});

/**
 * Mirror HandleInertiaRequests::version() (which hashes public/build/manifest.json).
 * Partial-reload tests need to send this so the middleware doesn't 409.
 */
function currentInertiaVersion(): string
{
    return file_exists($m = public_path('build/manifest.json'))
        ? hash_file('xxh128', $m)
        : '';
}

it('renders the index page with the Inertia component and shared shape', function () {
    Account::factory()->for($this->user)->create([
        'account_type' => 'normal',
        'username' => 'Alpha',
    ]);

    $this->actingAs($this->user)
        ->get(route('accounts.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Index')
            ->has('accountTypes')
            ->has('accounts.data')
            ->has('filters', fn ($filters) => $filters
                ->where('username', '')
                ->where('account_types', [])
                ->where('per_page', 16),
            ),
        );
});

it('filters accounts by username substring', function () {
    Account::factory()->for($this->user)->create(['username' => 'Foo', 'account_type' => 'normal']);
    Account::factory()->for($this->user)->create(['username' => 'Bar', 'account_type' => 'normal']);

    $this->actingAs($this->user)
        ->get(route('accounts.index', ['username' => 'Foo']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Index')
            ->has('accounts.data', 1)
            ->where('accounts.data.0.username', 'Foo'),
        );
});

it('filters accounts by username case-insensitively', function () {
    Account::factory()->for($this->user)->create(['username' => 'ZezimA', 'account_type' => 'normal']);
    Account::factory()->for($this->user)->create(['username' => 'Bar', 'account_type' => 'normal']);

    $this->actingAs($this->user)
        ->get(route('accounts.index', ['username' => 'zezima']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('accounts.data', 1)
            ->where('accounts.data.0.username', 'ZezimA'),
        );
});

it('paginates and exposes meta for the pager', function () {
    Account::factory()->count(20)->for($this->user)->create(['account_type' => 'normal']);

    $this->actingAs($this->user)
        ->get(route('accounts.index', ['per_page' => 16]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('accounts.data', 16)
            ->where('accounts.meta.last_page', 2)
            ->where('accounts.meta.per_page', 16)
            ->has('accounts.meta.links'),
        );
});

it('filters accounts by account_types[]', function () {
    Account::factory()->for($this->user)->create(['username' => 'IronOne', 'account_type' => 'ironman']);
    Account::factory()->for($this->user)->create(['username' => 'NormOne', 'account_type' => 'normal']);

    $this->actingAs($this->user)
        ->get(route('accounts.index', ['account_types' => ['ironman']]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('accounts.data', 1)
            ->where('accounts.data.0.username', 'IronOne'),
        );
});

it('rejects an account_type not in the enum', function () {
    $this->actingAs($this->user)
        ->get(route('accounts.index', ['account_types' => ['not-a-real-type']]))
        ->assertSessionHasErrors(['account_types.0']);
});

it('show renders the Inertia component with the `account` prop and per-tab data slots', function () {
    $account = Account::factory()->for($this->user)->create(['username' => 'Solo']);

    $this->actingAs($this->user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Show')
            ->where('account.username', 'Solo')
            ->where('inventory', null)
            ->where('bank', null)
            ->where('lootingBag', null)
            ->where('quests', null)
            ->where('recentLoot', [])
            // No position shared yet → null (drives the minimap empty state).
            ->where('position', null)
            // collectionLog is deferred — not present on the initial render
            ->missing('collectionLog'),
        );
});

it('exposes the last known position for the minimap', function () {
    $account = Account::factory()->for($this->user)->create([
        'username' => 'Wanderer',
        'world_x' => 3257,
        'world_y' => 3227,
        'world_plane' => 0,
    ]);

    $this->actingAs($this->user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('position.x', 3257)
            ->where('position.y', 3227)
            ->where('position.plane', 0),
        );
});

it('exposes recentLoot newest-first with hydrated item names where available', function () {
    Loot::query()->delete();
    $account = Account::factory()->for($this->user)->create(['username' => 'Looter']);

    Loot::create([
        'account_id' => $account->id,
        'source' => 'Older Kill',
        'items' => [['id' => 4151, 'quantity' => 1]],
        'total_value' => 1000,
        'killed_at' => now()->subHours(2),
    ]);
    Loot::create([
        'account_id' => $account->id,
        'source' => 'Newer Kill',
        'items' => [['id' => 4151, 'quantity' => 2]],
        'total_value' => 2000,
        'killed_at' => now()->subMinutes(5),
    ]);

    $this->actingAs($this->user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('recentLoot', 2)
            ->where('recentLoot.0.source', 'Newer Kill')
            ->where('recentLoot.1.source', 'Older Kill'),
        );

    Loot::query()->delete();
});

it('exposes a freshness prop with per-data-type timestamps and the staleness threshold', function () {
    config()->set('runemanager.freshness.stale_after_minutes', 90);
    $account = Account::factory()->for($this->user)->create(['username' => 'Fresh']);

    $this->actingAs($this->user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('freshness', fn ($freshness) => $freshness
                ->where('stale_after_minutes', 90)
                ->where('hiscores', null)
                ->where('inventory', null)
                ->where('bank', null)
                ->where('looting_bag', null)
                ->where('quests', null)
                ->where('equipment', null)
                ->where('avatar', null)
                ->where('loot', null),
            ),
        );
});

it('reports the hiscores timestamp once a sync has happened', function () {
    $account = Account::factory()->for($this->user)->create(['username' => 'Synced']);
    AccountHiscore::create([
        'account_id' => $account->id,
        'entries' => ['skills' => [], 'activities' => []],
        'fetched_at' => now()->subMinutes(5),
    ]);

    $this->actingAs($this->user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('freshness.hiscores'),
        );
});

it('exposes accountSearchResults as an optional shared prop (absent unless requested)', function () {
    Account::factory()->for($this->user)->create(['username' => 'Needle One']);

    $this->actingAs($this->user)
        ->get(route('accounts.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('accountSearchResults'),
        );
});

it('returns matching accounts when accountSearchResults is requested via partial reload', function () {
    Account::factory()->for($this->user)->create(['username' => 'Needle One']);
    Account::factory()->for($this->user)->create(['username' => 'OtherGuy']);

    // Partial-reload responses only carry the requested props, so we inspect the raw
    // payload rather than going through AssertableInertia (which expects a full page).
    $response = $this->actingAs($this->user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => currentInertiaVersion(),
            'X-Inertia-Partial-Data' => 'accountSearchResults',
            'X-Inertia-Partial-Component' => 'Accounts/Index',
        ])
        ->get(route('accounts.index', ['account_search' => 'Needle']))
        ->assertOk();

    $results = $response->json('props.accountSearchResults');
    expect($results)->toBeArray()->toHaveCount(1)
        ->and($results[0]['username'])->toBe('Needle One');
});
