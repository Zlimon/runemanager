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

it('treats the first save as setup without wiping anything', function () {
    $admin = owner();
    Account::factory()->for($admin)->create(['username' => 'Existing']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CLAN,
        ])
        ->assertRedirect();

    expect(Instance::mode())->toBe(Instance::MODE_CLAN);
    expect(Instance::isConfigured())->toBeTrue();
    expect(Account::where('username', 'Existing')->exists())->toBeTrue();
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
