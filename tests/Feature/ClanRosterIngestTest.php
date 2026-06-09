<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function ownerHeaders(): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'hash-owner',
        'X-Account-Username' => 'ClanOwner',
    ];
}

function rosterUser(string $role = Roles::OWNER): User
{
    Roles::sync();

    return tap(User::factory()->withPersonalTeam()->create())->assignRole($role);
}

beforeEach(function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
});

it('pre-creates unclaimed accounts from the roster and sets the clan name', function () {
    Sanctum::actingAs(rosterUser(Roles::OWNER));

    $this->postJson('/api/plugin/clan/roster', [
        'clan_name' => 'Knights of Falador',
        'members' => [
            ['username' => 'Zezima', 'rank' => 126, 'title' => 'Owner'],
            ['username' => 'Woox', 'rank' => 50, 'title' => 'General'],
        ],
    ], ownerHeaders())->assertSuccessful()->assertJsonPath('data.synced', 2);

    expect(Instance::name())->toBe('Knights of Falador');

    $zezima = Account::where('username', 'Zezima')->firstOrFail();
    expect($zezima->user_id)->toBeNull();
    expect($zezima->account_hash)->toBeNull();
    expect($zezima->clan_rank)->toBe(126);
    expect($zezima->clan_title)->toBe('Owner');
});

it('refreshes rank/title on an existing roster account without re-running on every push', function () {
    Sanctum::actingAs(rosterUser(Roles::OWNER));

    $this->postJson('/api/plugin/clan/roster', [
        'clan_name' => 'Knights of Falador',
        'members' => [['username' => 'Woox', 'rank' => 50, 'title' => 'General']],
    ], ownerHeaders())->assertSuccessful();

    $this->postJson('/api/plugin/clan/roster', [
        'clan_name' => 'Knights of Falador',
        'members' => [['username' => 'Woox', 'rank' => 100, 'title' => 'Admin']],
    ], ownerHeaders())->assertSuccessful();

    expect(Account::where('username', 'Woox')->count())->toBe(1);
    $woox = Account::where('username', 'Woox')->firstOrFail();
    expect($woox->clan_rank)->toBe(100);
});

it('refreshes the rank/title on a claimed account without changing the role', function () {
    $member = rosterUser(Roles::USER);
    Account::factory()->for($member)->create(['username' => 'Linked', 'clan_rank' => 1]);

    Sanctum::actingAs(rosterUser(Roles::OWNER));

    $this->postJson('/api/plugin/clan/roster', [
        'clan_name' => 'Knights of Falador',
        'members' => [['username' => 'Linked', 'rank' => 125, 'title' => 'Deputy Owner']],
    ], ownerHeaders())->assertSuccessful();

    $linked = Account::where('username', 'Linked')->firstOrFail();
    expect($linked->clan_rank)->toBe(125);
    // Clan-rank → role elevation is deferred; the member stays a plain User.
    expect($member->fresh()->hasRole(Roles::USER))->toBeTrue();
});

it('forbids non-admins from pushing a roster', function () {
    Sanctum::actingAs(rosterUser(Roles::USER));

    $this->postJson('/api/plugin/clan/roster', [
        'clan_name' => 'Knights of Falador',
        'members' => [['username' => 'Zezima', 'rank' => 1]],
    ], ownerHeaders())->assertForbidden();

    expect(Account::where('username', 'Zezima')->exists())->toBeFalse();
});

it('does nothing outside clan mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP);
    Sanctum::actingAs(rosterUser(Roles::OWNER));

    $this->postJson('/api/plugin/clan/roster', [
        'clan_name' => 'Knights of Falador',
        'members' => [['username' => 'Zezima', 'rank' => 1]],
    ], ownerHeaders())->assertSuccessful()->assertJsonPath('data.synced', 0);

    expect(Account::where('username', 'Zezima')->exists())->toBeFalse();
});

it('validates roster member entries', function () {
    Sanctum::actingAs(rosterUser(Roles::OWNER));

    $this->postJson('/api/plugin/clan/roster', [
        'members' => [['rank' => 1]], // missing username
    ], ownerHeaders())->assertJsonValidationErrors('members.0.username');
});
