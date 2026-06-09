<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function groupAdmin(): User
{
    foreach (['owner', 'admin', 'member'] as $name) {
        Role::findOrCreate($name, 'web');
    }

    return tap(User::factory()->withPersonalTeam()->create())->assignRole('owner');
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

it('renders the members page with the roster', function () {
    $admin = groupAdmin();
    Account::factory()->create(['username' => 'Listed', 'user_id' => null]);

    $this->actingAs($admin)
        ->get(route('admin.members'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Members')
            ->where('manageable', true)
            ->has('accounts', 1)
            ->where('accounts.0.username', 'Listed'),
        );
});
