<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\Bank;
use App\Models\CollectionLog;
use App\Models\GroupBank;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Models\User;
use App\Services\Hiscores\GroupIronmanValidator;
use App\Support\Instance;
use App\Support\Roles;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function owner(): User
{
    Roles::sync();

    return tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::OWNER);
}

it('completes first-time setup into a roster mode with no accounts and no confirmation', function () {
    SettingHelper::setSetting('instance_configured', false, 'bool');
    $admin = owner();

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CLAN,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Instance::mode())->toBe(Instance::MODE_CLAN);
    expect(Instance::isConfigured())->toBeTrue();
});

it('wipes account data when switching from casual to a roster mode', function () {
    // Default mode is casual and the instance has never been configured.
    $admin = owner();
    $member = tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::USER);
    Account::factory()->for($member)->create(['username' => 'Casualacc']);

    // Confirmation is required because there are accounts to delete.
    $this->actingAs($admin)
        ->put(route('admin.settings.update'), ['instance_mode' => Instance::MODE_CLAN])
        ->assertSessionHasErrors('confirm');
    expect(Account::count())->toBe(1);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CLAN,
            'confirm' => Instance::MODE_CLAN,
        ])
        ->assertRedirect();

    expect(Instance::mode())->toBe(Instance::MODE_CLAN);
    expect(Account::count())->toBe(0);
});

it('blocks a mode change without the typed confirmation', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    SettingHelper::setSetting('instance_configured', true, 'bool');
    $admin = owner();
    Account::factory()->for($admin)->create(['username' => 'Keepme']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_GROUP,
            'confirm' => 'wrong',
        ])
        ->assertSessionHasErrors('confirm');

    expect(Instance::mode())->toBe(Instance::MODE_CLAN);
    expect(Account::where('username', 'Keepme')->exists())->toBeTrue();
});

it('wipes account data and resets non-owner roles on a confirmed mode change', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    SettingHelper::setSetting('instance_configured', true, 'bool');

    $admin = owner();
    $member = tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::USER);
    $account = Account::factory()->for($member)->create(['username' => 'Wipeme']);
    AccountHiscore::forceCreate([
        'account_id' => $account->id,
        'entries' => [],
        'fetched_at' => now(),
    ]);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_GROUP,
            'confirm' => Instance::MODE_GROUP,
        ])
        ->assertRedirect();

    expect(Instance::mode())->toBe(Instance::MODE_GROUP);
    expect(Account::count())->toBe(0);
    expect(AccountHiscore::count())->toBe(0);
    expect($member->fresh()->hasRole(Roles::USER))->toBeTrue();
    // The owner is never demoted.
    expect($admin->fresh()->hasRole(Roles::OWNER))->toBeTrue();
});

it('does not wipe account data when switching to casual mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    SettingHelper::setSetting('instance_configured', true, 'bool');

    $admin = owner();
    $member = tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::USER);
    Account::factory()->for($member)->create(['username' => 'Keepme']);

    // No confirmation needed — switching to casual is non-destructive.
    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CASUAL,
        ])
        ->assertRedirect();

    expect(Instance::mode())->toBe(Instance::MODE_CASUAL);
    expect(Account::where('username', 'Keepme')->exists())->toBeTrue();
});

it('does not wipe when saving other settings without a mode change', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    SettingHelper::setSetting('instance_configured', true, 'bool');
    $admin = owner();
    Account::factory()->for($admin)->create(['username' => 'Survivor']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CLAN,
            'clan_name' => 'New Name',
        ])
        ->assertRedirect();

    expect(Account::where('username', 'Survivor')->exists())->toBeTrue();
    expect(Instance::name())->toBe('New Name');
});

it('forbids non-admins from changing settings', function () {
    Roles::sync();
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);
    $member = tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::USER);

    $this->actingAs($member)
        ->put(route('admin.settings.update'), ['instance_mode' => Instance::MODE_CLAN])
        ->assertForbidden();
});

it('reports a missing GIM group before the mode-switch confirmation and wipes nothing', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    SettingHelper::setSetting('instance_configured', true, 'bool');
    $admin = owner();
    Account::factory()->for($admin)->create(['username' => 'Keepme']);

    $http = new Client(['handler' => HandlerStack::create(new MockHandler([
        new Response(200, [], 'Unable to find group with name <strong>nope</strong>.'),
    ]))]);
    app()->instance(GroupIronmanValidator::class, new GroupIronmanValidator($http));

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_GROUP,
            'group_name' => ' nope ',
        ])
        ->assertSessionHasErrors(['group_name' => 'No Group Ironman group named "nope" was found on the OSRS hiscores.'])
        ->assertSessionDoesntHaveErrors('confirm');

    expect(Instance::mode())->toBe(Instance::MODE_CLAN);
    expect(Account::where('username', 'Keepme')->exists())->toBeTrue();
});

it('accepts a padded, differently cased confirmation and flashes the reset', function () {
    $admin = owner();
    Account::factory()->for($admin)->create(['username' => 'Casualacc']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), ['instance_mode' => Instance::MODE_CLAN, 'confirm' => ' CLAN '])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Instance reset and reconfigured.');

    expect(Account::count())->toBe(0);
});

it('rejects a wrong confirmation with the typed-mode message', function () {
    $admin = owner();
    Account::factory()->for($admin)->create(['username' => 'Casualacc']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), ['instance_mode' => Instance::MODE_CLAN, 'confirm' => 'group'])
        ->assertSessionHasErrors(['confirm' => 'Type the new mode name to confirm — this deletes all account data.']);
});

it('flashes a plain update when nothing was wiped', function () {
    SettingHelper::setSetting('instance_configured', false, 'bool');

    $this->actingAs(owner())
        ->put(route('admin.settings.update'), ['instance_mode' => Instance::MODE_CLAN])
        ->assertSessionHas('status', 'Instance settings updated.');
});

it('wipes every MongoDB collection, including the shared group bank, on a confirmed mode change', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP);
    SettingHelper::setSetting('instance_configured', true, 'bool');

    $admin = owner();
    $account = Account::factory()->for($admin)->create(['username' => 'Wipeme']);

    Inventory::forceCreate(['account_id' => $account->id, 'inventory' => []]);
    Bank::forceCreate(['account_id' => $account->id, 'bank' => []]);
    LootingBag::forceCreate(['account_id' => $account->id, 'looting_bag' => []]);
    Quest::forceCreate(['account_id' => $account->id, 'quests' => []]);
    CollectionLog::forceCreate(['account_id' => $account->id, 'items' => []]);
    Loot::forceCreate([
        'account_id' => $account->id,
        'source' => 'Zulrah',
        'type' => 'npc',
        'items' => [],
        'total_value' => 0,
    ]);
    // Instance-wide, not account-scoped — the new group must not inherit it.
    GroupBank::forceCreate(['items' => [[995, 1000]]]);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CLAN,
            'confirm' => Instance::MODE_CLAN,
        ])
        ->assertRedirect();

    expect(Account::count())->toBe(0);
    expect(Inventory::count())->toBe(0);
    expect(Bank::count())->toBe(0);
    expect(LootingBag::count())->toBe(0);
    expect(Quest::count())->toBe(0);
    expect(CollectionLog::count())->toBe(0);
    expect(Loot::count())->toBe(0);
    expect(GroupBank::count())->toBe(0);
});
