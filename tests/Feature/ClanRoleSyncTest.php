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

function clanMember(): User
{
    foreach (['owner', 'admin', 'member'] as $role) {
        Role::findOrCreate($role, 'web');
    }

    return User::factory()->withPersonalTeam()->create();
}

beforeEach(function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    SettingHelper::setSetting('clan_name', 'Knights of Falador');
});

it('stores the clan name, rank and title on the account', function () {
    Sanctum::actingAs(clanMember());

    $this->putJson('/api/plugin/clan', [
        'clan_name' => 'Knights of Falador',
        'clan_rank' => 100,
        'clan_title' => 'General',
    ], clanHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    expect($account->clan_name)->toBe('Knights of Falador');
    expect($account->clan_rank)->toBe(100);
    expect($account->clan_title)->toBe('General');
});

it('promotes a member at administrator rank or above to admin', function () {
    $user = clanMember();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/clan', [
        'clan_name' => 'Knights of Falador',
        'clan_rank' => 126, // OWNER
        'clan_title' => 'Owner',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole('admin'))->toBeTrue();
});

it('keeps a low-rank clan member as a plain member', function () {
    $user = clanMember();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/clan', [
        'clan_name' => 'Knights of Falador',
        'clan_rank' => 50, // custom rank below ADMINISTRATOR
        'clan_title' => 'Corporal',
    ], clanHeaders())->assertSuccessful();

    $user = $user->fresh();
    expect($user->hasRole('member'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeFalse();
});

it('demotes a former clan-admin back to member when their rank drops', function () {
    $user = clanMember();
    $user->assignRole('admin');
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/clan', [
        'clan_name' => 'Knights of Falador',
        'clan_rank' => 1,
        'clan_title' => 'Recruit',
    ], clanHeaders())->assertSuccessful();

    $user = $user->fresh();
    expect($user->hasRole('admin'))->toBeFalse();
    expect($user->hasRole('member'))->toBeTrue();
});

it('never demotes the instance owner', function () {
    $user = clanMember();
    $user->assignRole('owner');
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/clan', [
        'clan_name' => 'Knights of Falador',
        'clan_rank' => 1,
        'clan_title' => 'Recruit',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole('owner'))->toBeTrue();
});

it('ignores accounts from a different clan', function () {
    $user = clanMember();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/clan', [
        'clan_name' => 'Some Other Clan',
        'clan_rank' => 126,
        'clan_title' => 'Owner',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole('admin'))->toBeFalse();
});

it('does not sync roles outside clan mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);
    $user = clanMember();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/clan', [
        'clan_name' => 'Knights of Falador',
        'clan_rank' => 126,
        'clan_title' => 'Owner',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole('admin'))->toBeFalse();
});

it('takes the highest rank across the users accounts', function () {
    $user = clanMember();
    Account::factory()->for($user)->create([
        'username' => 'AltAccount',
        'clan_name' => 'Knights of Falador',
        'clan_rank' => 126, // OWNER on the alt
    ]);
    Sanctum::actingAs($user);

    // The main account pushes a low rank, but the alt is clan owner.
    $this->putJson('/api/plugin/clan', [
        'clan_name' => 'Knights of Falador',
        'clan_rank' => 1,
        'clan_title' => 'Recruit',
    ], clanHeaders())->assertSuccessful();

    expect($user->fresh()->hasRole('admin'))->toBeTrue();
});

it('requires authentication', function () {
    $this->putJson('/api/plugin/clan', ['clan_name' => 'x'], ['Accept' => 'application/json'])
        ->assertUnauthorized();
});

it('validates the clan rank range', function () {
    Sanctum::actingAs(clanMember());

    $this->putJson('/api/plugin/clan', ['clan_rank' => 9999], clanHeaders())
        ->assertJsonValidationErrorFor('clan_rank');
});
