<?php

use App\Events\GroupBankUpdated;
use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\GroupBank;
use App\Models\User;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function groupBankHeaders(string $hash = 'hash-gb', string $username = 'GimGuy'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

function groupMember(): User
{
    Roles::sync();
    $user = tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::OWNER);
    Account::factory()->for($user)->create([
        'username' => 'GimGuy',
        'account_hash' => 'hash-gb',
        'account_type' => 'group_ironman',
    ]);

    return $user;
}

beforeEach(function () {
    GroupBank::query()->delete();
    SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP);
    SettingHelper::setSetting('group_name', 'The Group');
});

it('stores the shared group bank and broadcasts the update in group mode', function () {
    Event::fake([GroupBankUpdated::class]);
    Sanctum::actingAs(groupMember());

    $this->putJson('/api/plugin/group-bank', [
        'group_bank' => [[995, 1000000], [4151, 1]],
    ], groupBankHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.stored', true)
        ->assertJsonPath('data.slots', 2);

    $bank = GroupBank::query()->firstOrFail();
    expect($bank->items)->toBe([[995, 1000000], [4151, 1]]);

    Event::assertDispatched(GroupBankUpdated::class);
});

it('overwrites the single shared document on each push', function () {
    Sanctum::actingAs(groupMember());

    $this->putJson('/api/plugin/group-bank', ['group_bank' => [[995, 5]]], groupBankHeaders())->assertSuccessful();
    $this->putJson('/api/plugin/group-bank', ['group_bank' => [[4151, 1]]], groupBankHeaders())->assertSuccessful();

    expect(GroupBank::query()->count())->toBe(1);
    expect(GroupBank::query()->firstOrFail()->items)->toBe([[4151, 1]]);
});

it('ignores the push outside group mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);
    Sanctum::actingAs(groupMember());

    $this->putJson('/api/plugin/group-bank', ['group_bank' => [[995, 5]]], groupBankHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.stored', false);

    expect(GroupBank::query()->count())->toBe(0);
});

it('requires authentication', function () {
    $this->putJson('/api/plugin/group-bank', ['group_bank' => []], ['Accept' => 'application/json'])
        ->assertUnauthorized();
});

it('renders the group bank page with enriched items in group mode', function () {
    GroupBank::query()->create(['items' => [[995, 1000000]]]);
    $user = groupMember();

    $this->actingAs($user)
        ->get(route('group-bank.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('GroupBank/Index')
            ->where('groupName', 'The Group')
            ->has('groupBank.items', 1)
            ->where('groupBank.items.0.quantity', 1000000),
        );
});

it('404s the group bank page outside group mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);

    $this->actingAs(groupMember())
        ->get(route('group-bank.index'))
        ->assertNotFound();
});
