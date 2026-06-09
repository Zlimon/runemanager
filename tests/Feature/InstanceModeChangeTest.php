<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function owner(): User
{
    foreach (['owner', 'admin', 'member'] as $name) {
        Role::findOrCreate($name, 'web');
    }

    return tap(User::factory()->withPersonalTeam()->create())->assignRole('owner');
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
    $member = tap(User::factory()->withPersonalTeam()->create())->assignRole('member');
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
    $member = tap(User::factory()->withPersonalTeam()->create())->assignRole('admin');
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
    expect($member->fresh()->hasRole('member'))->toBeTrue();
    expect($member->fresh()->hasRole('admin'))->toBeFalse();
    // The owner is never demoted.
    expect($admin->fresh()->hasRole('owner'))->toBeTrue();
});

it('does not wipe account data when switching to casual mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    SettingHelper::setSetting('instance_configured', true, 'bool');

    $admin = owner();
    $member = tap(User::factory()->withPersonalTeam()->create())->assignRole('admin');
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
    foreach (['owner', 'admin', 'member'] as $name) {
        Role::findOrCreate($name, 'web');
    }
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);
    $member = tap(User::factory()->withPersonalTeam()->create())->assignRole('member');

    $this->actingAs($member)
        ->put(route('admin.settings.update'), ['instance_mode' => Instance::MODE_CLAN])
        ->assertForbidden();
});
