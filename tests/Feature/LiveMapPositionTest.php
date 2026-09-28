<?php

use App\Events\AccountMoved;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function mapUser(): User
{
    return User::query()->forceCreate([
        'name' => 'Map User',
        'email' => 'map@test.local',
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function mapHeaders(string $hash = 'hash-map', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

it('stores the latest position on a plugin push and broadcasts it', function () {
    Event::fake([AccountMoved::class]);
    Sanctum::actingAs(mapUser());

    $this->putJson('/api/plugin/position', ['x' => 3221, 'y' => 3219, 'plane' => 0], mapHeaders())
        ->assertSuccessful();

    $account = Account::firstOrFail();
    expect($account->world_x)->toBe(3221);
    expect($account->world_y)->toBe(3219);
    expect($account->world_plane)->toBe(0);
    expect($account->position_updated_at)->not->toBeNull();
    expect($account->isOnMap())->toBeTrue();

    Event::assertDispatched(AccountMoved::class, fn (AccountMoved $event): bool => $event->account->is($account));
});

it('broadcasts the position on the private map channel with a light payload', function () {
    Sanctum::actingAs(mapUser());

    $account = Account::factory()->create([
        'username' => 'Zezima',
        'account_type' => 'normal',
        'world_x' => 3200,
        'world_y' => 3200,
        'world_plane' => 0,
    ]);
    $event = new AccountMoved($account);

    expect($event->broadcastOn()[0]->name)->toBe('private-map');
    expect($event->broadcastAs())->toBe('AccountMoved');
    expect($event->broadcastWith())->toBe([
        'username' => 'Zezima',
        'account_type' => 'normal',
        'x' => 3200,
        'y' => 3200,
        'plane' => 0,
    ]);
});

it('rejects an out-of-range plane', function () {
    Sanctum::actingAs(mapUser());

    $this->putJson('/api/plugin/position', ['x' => 1, 'y' => 2, 'plane' => 9], mapHeaders())
        ->assertJsonValidationErrorFor('plane');
});

it('requires authentication', function () {
    $this->putJson('/api/plugin/position', ['x' => 1, 'y' => 2, 'plane' => 0], ['Accept' => 'application/json'])
        ->assertUnauthorized();
});

it('renders the map page with only currently-sharing accounts', function () {
    config()->set('runemanager.map.visible_within_minutes', 2);
    $user = User::factory()->withPersonalTeam()->create();

    Account::factory()->for($user)->create(['username' => 'Sharing', 'account_type' => 'normal', 'world_x' => 3200, 'world_y' => 3200, 'world_plane' => 0, 'position_updated_at' => now()]);
    Account::factory()->for($user)->create(['username' => 'Stale', 'world_x' => 1, 'world_y' => 1, 'world_plane' => 0, 'position_updated_at' => now()->subMinutes(30)]);

    $this->actingAs($user)
        ->get(route('map.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Map/Index')
            ->has('accounts', 1)
            ->where('accounts.0.username', 'Sharing')
            ->where('accounts.0.x', 3200)
            ->where('accounts.0.account_type', 'normal'),
        );
});

it('requires authentication to view the map page', function () {
    $this->get(route('map.index'))->assertRedirect(route('login'));
});

it('derives map presence within the window and drops stale positions', function () {
    config()->set('runemanager.map.visible_within_minutes', 2);

    $onMap = Account::factory()->create(['world_x' => 100, 'world_y' => 200, 'world_plane' => 0, 'position_updated_at' => now()->subMinute()]);
    $stale = Account::factory()->create(['world_x' => 100, 'world_y' => 200, 'world_plane' => 0, 'position_updated_at' => now()->subMinutes(10)]);
    $never = Account::factory()->create(['world_x' => null, 'position_updated_at' => null]);

    expect($onMap->isOnMap())->toBeTrue();
    expect($stale->isOnMap())->toBeFalse();
    expect($never->isOnMap())->toBeFalse();

    expect(Account::onMap()->pluck('id')->all())->toBe([$onMap->id]);
});

it('only reveals another member\'s last position through ?focus while they are sharing', function (bool $sharing, bool $isOwner, bool $visible) {
    config()->set('runemanager.map.visible_within_minutes', 2);
    $owner = User::factory()->withPersonalTeam()->create();
    $viewer = $isOwner ? $owner : User::factory()->withPersonalTeam()->create();

    Account::factory()->for($owner)->create([
        'username' => 'Wanderer',
        'world_x' => 3222,
        'world_y' => 3218,
        'world_plane' => 1,
        'position_updated_at' => $sharing ? now() : now()->subHours(3),
    ]);

    $this->actingAs($viewer)
        ->get(route('map.index', ['focus' => 'Wanderer']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('focus', $visible ? ['x' => 3222, 'y' => 3218, 'plane' => 1] : null),
        );
})->with([
    'another member, still sharing' => [true, false, true],
    'another member, stopped sharing' => [false, false, false],
    'the owner, stopped sharing' => [false, true, true],
]);

it('hides a stale position from the account header for everyone but the owner', function (bool $sharing, bool $isOwner, bool $visible) {
    config()->set('runemanager.map.visible_within_minutes', 2);
    $owner = User::factory()->withPersonalTeam()->create();
    $viewer = $isOwner ? $owner : User::factory()->withPersonalTeam()->create();

    $account = Account::factory()->for($owner)->create([
        'username' => 'Wanderer',
        'world_x' => 3222,
        'world_y' => 3218,
        'world_plane' => 0,
        'position_updated_at' => $sharing ? now() : now()->subHours(3),
    ]);

    foreach (['accounts.show', 'accounts.items', 'accounts.collection'] as $routeName) {
        $this->actingAs($viewer)
            ->get(route($routeName, $account))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('position', $visible ? ['x' => 3222, 'y' => 3218, 'plane' => 0] : null),
            );
    }
})->with([
    'another member, still sharing' => [true, false, true],
    'another member, stopped sharing' => [false, false, false],
    'the owner, stopped sharing' => [false, true, true],
]);

it('never reveals the position of an account nobody has claimed once it stops sharing', function () {
    config()->set('runemanager.map.visible_within_minutes', 2);

    $account = Account::factory()->create([
        'user_id' => null,
        'world_x' => 3222,
        'world_y' => 3218,
        'world_plane' => 0,
        'position_updated_at' => now()->subHours(3),
    ]);

    expect($account->livePositionFor(null))->toBeNull()
        ->and($account->livePositionFor(User::factory()->create()))->toBeNull();
});
