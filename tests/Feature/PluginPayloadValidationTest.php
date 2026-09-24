<?php

use App\Helpers\SettingHelper;
use App\Models\User;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function payloadHeaders(): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'hash-payload',
        'X-Account-Username' => 'Zlimon',
    ];
}

it('rejects a malformed plugin push with a 422 on the offending key', function (string $method, string $uri, array $payload, string $errorKey) {
    Sanctum::actingAs(User::factory()->create());

    $this->json($method, $uri, $payload, payloadHeaders())
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errorKey);
})->with([
    'bank missing' => ['PUT', '/api/plugin/bank', [], 'bank'],
    'inventory item id not an integer' => ['PUT', '/api/plugin/inventory', ['inventory' => [['coins', 1]]], 'inventory.0.0'],
    'looting bag item id not an integer' => ['PUT', '/api/plugin/looting-bag', ['looting_bag' => [['coins', 1]]], 'looting_bag.0.0'],
    'equipment item id not an integer' => ['PUT', '/api/plugin/equipment', ['equipment' => [['whip']]], 'equipment.0.0'],
    'quest status not an integer' => ['PUT', '/api/plugin/quests', ['quests' => [['Cook\'s Assistant', 'done']]], 'quests.0.1'],
    'diaries missing' => ['PUT', '/api/plugin/diaries', [], 'diaries'],
    'group bank slot not a pair' => ['PUT', '/api/plugin/group-bank', ['group_bank' => [[995, 1, 2]]], 'group_bank.0'],
    'combat achievement points negative' => ['PUT', '/api/plugin/combat-achievements', ['points' => -1, 'tiers' => []], 'points'],
    'position plane out of range' => ['PUT', '/api/plugin/position', ['x' => 3221, 'y' => 3219, 'plane' => 4], 'plane'],
    'vitals run energy above 100' => ['PUT', '/api/plugin/vitals', [
        'hitpoints' => 10, 'hitpoints_max' => 10, 'prayer' => 1, 'prayer_max' => 1, 'run_energy' => 101, 'special_attack' => 100,
    ], 'run_energy'],
    'status world zero' => ['PUT', '/api/plugin/status', ['world' => 0], 'world'],
    'clan rank out of range' => ['PUT', '/api/plugin/clan', ['clan_rank' => 200], 'clan_rank'],
]);

it('forbids a non-admin roster push before validating its payload', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    Roles::sync();
    Sanctum::actingAs(tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::USER));

    $this->postJson('/api/plugin/clan/roster', ['members' => 'not-an-array'], payloadHeaders())
        ->assertForbidden()
        ->assertJson(['message' => 'You do not have permission to seed the clan roster.'])
        ->assertJsonMissingValidationErrors('members');
});
