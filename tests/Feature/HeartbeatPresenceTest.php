<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function heartbeatUser(): User
{
    return User::query()->forceCreate([
        'name' => 'Plugin User',
        'email' => 'heartbeat@test.local',
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function heartbeatHeaders(string $hash = 'hash-hb', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

it('stamps last_seen_at on heartbeat', function () {
    Sanctum::actingAs(heartbeatUser());

    $this->putJson('/api/plugin/heartbeat', [], heartbeatHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    expect($account->last_seen_at)->not->toBeNull();
    expect($account->isOnline())->toBeTrue();
});

it('requires authentication', function () {
    $this->putJson('/api/plugin/heartbeat', [], ['Accept' => 'application/json'])->assertUnauthorized();
});

it('derives online within the presence window and offline past it', function () {
    config()->set('runemanager.presence.online_within_minutes', 3);

    $online = Account::factory()->create(['last_seen_at' => now()->subMinute()]);
    $offline = Account::factory()->create(['last_seen_at' => now()->subMinutes(10)]);
    $never = Account::factory()->create(['last_seen_at' => null]);

    expect($online->isOnline())->toBeTrue();
    expect($offline->isOnline())->toBeFalse();
    expect($never->isOnline())->toBeFalse();
});
