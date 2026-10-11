<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\Bank;
use App\Models\CollectionLog;
use App\Models\GroupBank;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Models\User;
use App\Support\Instance;
use App\Support\Roles;

function groupAdmin(): User
{
    Roles::sync();

    return tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::OWNER);
}

beforeEach(function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP);
});

it('lets the admin add a member as an unclaimed account', function () {
    $this->actingAs(groupAdmin())
        ->post(route('admin.members.store'), ['username' => 'Woox'])
        ->assertRedirect();

    $account = Account::where('username', 'Woox')->firstOrFail();
    expect($account->user_id)->toBeNull();
    expect($account->account_hash)->toBeNull();
    // Placeholder until the member's first plugin login confirms the type.
    expect($account->account_type->value)->toBe('group_ironman');
});

it('rejects a duplicate username', function () {
    $admin = groupAdmin();
    Account::factory()->create(['username' => 'Taken', 'user_id' => null]);

    $this->actingAs($admin)
        ->post(route('admin.members.store'), ['username' => 'Taken'])
        ->assertSessionHasErrors('username');
});

it('rejects an invalid username', function () {
    $this->actingAs(groupAdmin())
        ->post(route('admin.members.store'), ['username' => 'bad@name!'])
        ->assertSessionHasErrors('username');
});

it('removes a member account', function () {
    $admin = groupAdmin();
    $account = Account::factory()->create(['username' => 'Gone', 'user_id' => null]);

    $this->actingAs($admin)
        ->delete(route('admin.members.destroy', $account->id))
        ->assertRedirect();

    expect(Account::find($account->id))->toBeNull();
});

it('does not allow member management in casual mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);

    $this->actingAs(groupAdmin())
        ->post(route('admin.members.store'), ['username' => 'Nope'])
        ->assertForbidden();
});

it('refuses member management in casual mode before validating the username', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);

    $this->actingAs(groupAdmin())
        ->postJson(route('admin.members.store'), ['username' => 'bad@name!'])
        ->assertForbidden()
        ->assertJsonPath('message', 'Members are only managed in clan or group mode.');

    expect(Account::count())->toBe(0);
});

it('renders the members page with the roster', function () {
    $admin = groupAdmin();
    Account::factory()->create(['username' => 'Listed', 'user_id' => null]);

    $this->actingAs($admin)
        ->get(route('admin.members'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Members/Index')
            ->where('manageable', true)
            ->has('accounts', 1)
            ->where('accounts.0.username', 'Listed'),
        );
});

it('clears the removed member MongoDB snapshots but leaves the shared group bank', function () {
    $admin = groupAdmin();
    $account = Account::factory()->create(['username' => 'Gone', 'user_id' => null]);
    $keeper = Account::factory()->create(['username' => 'Stays', 'user_id' => null]);

    foreach ([$account, $keeper] as $subject) {
        Inventory::forceCreate(['account_id' => $subject->id, 'inventory' => []]);
        Bank::forceCreate(['account_id' => $subject->id, 'bank' => []]);
        LootingBag::forceCreate(['account_id' => $subject->id, 'looting_bag' => []]);
        Quest::forceCreate(['account_id' => $subject->id, 'quests' => []]);
        CollectionLog::forceCreate(['account_id' => $subject->id, 'items' => []]);
        Loot::forceCreate([
            'account_id' => $subject->id,
            'source' => 'Zulrah',
            'type' => 'npc',
            'items' => [],
            'total_value' => 0,
        ]);
    }

    GroupBank::forceCreate(['items' => [[995, 1000]]]);

    $this->actingAs($admin)
        ->delete(route('admin.members.destroy', $account->id))
        ->assertRedirect();

    expect(Account::find($account->id))->toBeNull();

    foreach ([Inventory::class, Bank::class, LootingBag::class, Quest::class, CollectionLog::class, Loot::class] as $model) {
        expect($model::where('account_id', $account->id)->count())->toBe(0);
        // The other member's snapshots are untouched.
        expect($model::where('account_id', $keeper->id)->count())->toBe(1);
    }

    // Instance-wide, so removing one member does not empty it.
    expect(GroupBank::count())->toBe(1);
});
