<?php

use App\Events\AccountStatusUpdated;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function statusUser(): User
{
    return User::query()->forceCreate([
        'name' => 'Status User',
        'email' => 'status@test.local',
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function statusHeaders(string $hash = 'hash-status', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

it('stores the activity and broadcasts it on the account + map channels', function () {
    Event::fake([AccountStatusUpdated::class]);
    Sanctum::actingAs(statusUser());

    $this->putJson('/api/plugin/status', ['activity' => 'Fighting Vorkath'], statusHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    expect($account->activity)->toBe('Fighting Vorkath');

    Event::assertDispatched(AccountStatusUpdated::class, function (AccountStatusUpdated $event) use ($account): bool {
        $channels = array_map(fn ($c) => $c->name, $event->broadcastOn());

        return $event->account->is($account)
            && $event->broadcastWith() === ['username' => $account->username, 'activity' => 'Fighting Vorkath']
            && in_array('private-account.'.$account->id, $channels, true)
            && in_array('private-map', $channels, true);
    });
});

it('accepts a null activity (idle/clear)', function () {
    Sanctum::actingAs(statusUser());

    $this->putJson('/api/plugin/status', ['activity' => null], statusHeaders())->assertSuccessful();

    expect(Account::firstOrFail()->activity)->toBeNull();
});

it('rejects an over-long activity', function () {
    Sanctum::actingAs(statusUser());

    $this->putJson('/api/plugin/status', ['activity' => str_repeat('x', 80)], statusHeaders())
        ->assertJsonValidationErrorFor('activity');
});

it('requires authentication', function () {
    $this->putJson('/api/plugin/status', ['activity' => 'Idle'], ['Accept' => 'application/json'])->assertUnauthorized();
});

it('exposes activity through the account resource', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create(['username' => 'Busy', 'activity' => 'Woodcutting']);

    $this->actingAs($user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('account.activity', 'Woodcutting'));
});
