<?php

use App\Events\AccountVitalsUpdated;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function vitalsUser(): User
{
    return User::query()->forceCreate([
        'name' => 'Vitals User',
        'email' => 'vitals@test.local',
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function vitalsHeaders(string $hash = 'hash-vit', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

$validPayload = [
    'hitpoints' => 62,
    'hitpoints_max' => 99,
    'prayer' => 40,
    'prayer_max' => 70,
    'run_energy' => 85,
    'special_attack' => 50,
];

it('stores vitals and broadcasts them', function () use ($validPayload) {
    Event::fake([AccountVitalsUpdated::class]);
    Sanctum::actingAs(vitalsUser());

    $this->putJson('/api/plugin/vitals', $validPayload, vitalsHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    expect($account->vitalsPayload())->toBe($validPayload);

    Event::assertDispatched(
        AccountVitalsUpdated::class,
        fn (AccountVitalsUpdated $event): bool => $event->account->is($account)
            && $event->broadcastWith() === $validPayload
            && $event->broadcastOn()[0]->name === 'private-account.'.$account->id,
    );
});

it('rejects an out-of-range run energy', function () use ($validPayload) {
    Sanctum::actingAs(vitalsUser());

    $this->putJson('/api/plugin/vitals', [...$validPayload, 'run_energy' => 150], vitalsHeaders())
        ->assertJsonValidationErrorFor('run_energy');
});

it('requires authentication', function () use ($validPayload) {
    $this->putJson('/api/plugin/vitals', $validPayload, ['Accept' => 'application/json'])->assertUnauthorized();
});

it('exposes vitals on the account show page', function () use ($validPayload) {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create(['username' => 'Combatant', ...$validPayload, 'vitals_updated_at' => now()]);

    $this->actingAs($user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('vitals', $validPayload));
});

it('returns null vitals until the plugin pushes them', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create();

    expect($account->vitalsPayload())->toBeNull();
});
