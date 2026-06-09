<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

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
 * A clan member with their primary account already claimed (the roster + claim
 * flow having run), acting as them. Clan mode no longer auto-creates on login.
 */
function actingClanMember(?string $role = null): User
{
    foreach (['owner', 'admin', 'member'] as $r) {
        Role::findOrCreate($r, 'web');
    }

    $user = User::factory()->withPersonalTeam()->create();
    if ($role) {
        $user->assignRole($role);
    }

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

it('promotes a member at administrator rank or above to admin', function () {
    $user = actingClanMember();

    $this->putJson('/api/plugin/clan', [
        'clan_rank' => 126, // OWNER
        'clan_title' => 'Owner',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole('admin'))->toBeTrue();
});

it('keeps a low-rank clan member as a plain member', function () {
    $user = actingClanMember();

    $this->putJson('/api/plugin/clan', [
        'clan_rank' => 50, // custom rank below ADMINISTRATOR
        'clan_title' => 'Corporal',
    ], clanHeaders())->assertSuccessful();

    $user = $user->fresh();
    expect($user->hasRole('member'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeFalse();
});

it('demotes a former clan-admin back to member when their rank drops', function () {
    $user = actingClanMember('admin');

    $this->putJson('/api/plugin/clan', [
        'clan_rank' => 1,
        'clan_title' => 'Recruit',
    ], clanHeaders())->assertSuccessful();

    $user = $user->fresh();
    expect($user->hasRole('admin'))->toBeFalse();
    expect($user->hasRole('member'))->toBeTrue();
});

it('never demotes the instance owner', function () {
    $user = actingClanMember('owner');

    $this->putJson('/api/plugin/clan', [
        'clan_rank' => 1,
        'clan_title' => 'Recruit',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole('owner'))->toBeTrue();
});

it('does not sync roles outside clan mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);
    $user = actingClanMember();

    $this->putJson('/api/plugin/clan', [
        'clan_rank' => 126,
        'clan_title' => 'Owner',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole('admin'))->toBeFalse();
});

it('takes the highest rank across the users accounts', function () {
    $user = actingClanMember();
    Account::factory()->for($user)->create([
        'username' => 'AltAccount',
        'clan_rank' => 126, // OWNER on the alt
    ]);

    // The main account pushes a low rank, but the alt is clan owner.
    $this->putJson('/api/plugin/clan', [
        'clan_rank' => 1,
        'clan_title' => 'Recruit',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole('admin'))->toBeTrue();
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
