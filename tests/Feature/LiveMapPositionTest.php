<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

it('stores the latest position on a plugin push', function () {
    Sanctum::actingAs(mapUser());

    $this->putJson('/api/plugin/position', ['x' => 3221, 'y' => 3219, 'plane' => 0], mapHeaders())
        ->assertSuccessful();

    $account = Account::firstOrFail();
    expect($account->world_x)->toBe(3221);
    expect($account->world_y)->toBe(3219);
    expect($account->world_plane)->toBe(0);
    expect($account->position_updated_at)->not->toBeNull();
    expect($account->isOnMap())->toBeTrue();
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
