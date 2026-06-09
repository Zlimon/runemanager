<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function clanHeaders(string $hash = 'hash-clan', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

/**
 * A clan member with their primary account already claimed, acting as them.
 * (Clan-rank → website-role mapping is deferred, so only the rank/title data is
 * stored for now.)
 */
function actingClanMember(): User
{
    Roles::sync();
    $user = tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::USER);
    Account::factory()->for($user)->create(['username' => 'Zlimon', 'account_hash' => 'hash-clan']);
    Sanctum::actingAs($user);

    return $user;
}

beforeEach(function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
});

it('stores the clan rank and title on the account', function () {
    actingClanMember();

    $this->putJson('/api/plugin/clan', [
        'clan_rank' => 100,
        'clan_title' => 'General',
    ], clanHeaders())->assertSuccessful();

    $account = Account::where('username', 'Zlimon')->firstOrFail();
    expect($account->clan_rank)->toBe(100);
    expect($account->clan_title)->toBe('General');
});

it('does not change the website role from the clan rank (deferred)', function () {
    $user = actingClanMember();

    $this->putJson('/api/plugin/clan', [
        'clan_rank' => 126, // OWNER in-game
        'clan_title' => 'Owner',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole(Roles::USER))->toBeTrue();
});

it('requires authentication', function () {
    $this->putJson('/api/plugin/clan', ['clan_rank' => 1], ['Accept' => 'application/json'])
        ->assertUnauthorized();
});

it('validates the clan rank range', function () {
    actingClanMember();

    $this->putJson('/api/plugin/clan', ['clan_rank' => 9999], clanHeaders())
        ->assertJsonValidationErrorFor('clan_rank');
});
