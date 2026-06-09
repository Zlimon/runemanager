<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function ownerHeaders(): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'hash-owner',
        'X-Account-Username' => 'ClanOwner',
    ];
}

function admin(string $role = 'admin'): User
{
    foreach (['owner', 'admin', 'member'] as $name) {
        Role::findOrCreate($name, 'web');
    }

    return tap(User::factory()->withPersonalTeam()->create())->assignRole($role);
}

beforeEach(function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
});

it('pre-creates unclaimed accounts from the roster and sets the clan name', function () {
    Sanctum::actingAs(admin('owner'));

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
    Sanctum::actingAs(admin('owner'));

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

it('promotes a claimed member when the roster gives them an admin rank', function () {
    $member = User::factory()->withPersonalTeam()->create();
    foreach (['owner', 'admin', 'member'] as $name) {
        Role::findOrCreate($name, 'web');
    }
    Account::factory()->for($member)->create(['username' => 'Linked', 'clan_rank' => 1]);

    Sanctum::actingAs(admin('owner'));

    $this->postJson('/api/plugin/clan/roster', [
        'clan_name' => 'Knights of Falador',
        'members' => [['username' => 'Linked', 'rank' => 125, 'title' => 'Deputy Owner']],
    ], ownerHeaders())->assertSuccessful();

    expect($member->fresh()->hasRole('admin'))->toBeTrue();
});

it('forbids non-admins from pushing a roster', function () {
    Sanctum::actingAs(admin('member'));

    $this->postJson('/api/plugin/clan/roster', [
        'clan_name' => 'Knights of Falador',
        'members' => [['username' => 'Zezima', 'rank' => 1]],
    ], ownerHeaders())->assertForbidden();

    expect(Account::where('username', 'Zezima')->exists())->toBeFalse();
});

it('does nothing outside clan mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP);
    $admin = admin('owner');
    // Pre-link the owner's account so the middleware resolves it by hash and
    // doesn't run the group-mode GIM check while reaching the controller.
    Account::factory()->for($admin)->create(['username' => 'ClanOwner', 'account_hash' => 'hash-owner']);
    Sanctum::actingAs($admin);

    $this->postJson('/api/plugin/clan/roster', [
        'clan_name' => 'Knights of Falador',
        'members' => [['username' => 'Zezima', 'rank' => 1]],
    ], ownerHeaders())->assertSuccessful()->assertJsonPath('data.synced', 0);

    expect(Account::where('username', 'Zezima')->exists())->toBeFalse();
});

it('validates roster member entries', function () {
    Sanctum::actingAs(admin('owner'));

    $this->postJson('/api/plugin/clan/roster', [
        'members' => [['rank' => 1]], // missing username
    ], ownerHeaders())->assertJsonValidationErrors('members.0.username');
});
