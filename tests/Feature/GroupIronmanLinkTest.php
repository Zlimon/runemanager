<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function gimHeaders(string $hash, string $username, ?string $type = null): array
{
    return array_filter([
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
        'X-Account-Type' => $type,
    ]);
}

beforeEach(fn () => SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP));

it('creates and links a GIM account on first plugin login in group mode', function () {
    $user = User::factory()->withPersonalTeam()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-g', 'GimGuy', 'group_ironman'))
        ->assertSuccessful();

    $account = Account::where('username', 'GimGuy')->firstOrFail();
    expect($account->user_id)->toBe($user->id);
    expect($account->account_type->value)->toBe('group_ironman');
});

it('rejects a non-GIM account on plugin login in group mode', function () {
    Sanctum::actingAs(User::factory()->withPersonalTeam()->create());

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-m', 'MainGuy', 'ironman'))
        ->assertForbidden();

    expect(Account::where('username', 'MainGuy')->exists())->toBeFalse();
});

it('rejects when the plugin reports no account type in group mode', function () {
    Sanctum::actingAs(User::factory()->withPersonalTeam()->create());

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-x', 'NoType'))
        ->assertForbidden();

    expect(Account::where('username', 'NoType')->exists())->toBeFalse();
});

it('rejects claiming an unclaimed account that is not a GIM', function () {
    $account = Account::factory()->create(['username' => 'Rostered', 'user_id' => null, 'account_hash' => null]);
    Sanctum::actingAs(User::factory()->withPersonalTeam()->create());

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-r', 'Rostered', 'normal'))
        ->assertForbidden();

    expect($account->fresh()->user_id)->toBeNull();
});

it('claims an unclaimed account when the player is a GIM', function () {
    $account = Account::factory()->create(['username' => 'Rostered', 'user_id' => null, 'account_hash' => null]);
    $user = User::factory()->withPersonalTeam()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-r', 'Rostered', 'group_ironman'))
        ->assertSuccessful();

    expect($account->fresh()->user_id)->toBe($user->id);
});

it('stores the reported account type in casual mode without validating', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);
    $user = User::factory()->withPersonalTeam()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-c', 'IronCasual', 'ironman'))
        ->assertSuccessful();

    expect(Account::where('username', 'IronCasual')->first()->account_type->value)->toBe('ironman');
});

it('keeps the stored type in step with a downgrade on a later login', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create([
        'username' => 'ExHardcore',
        'account_hash' => 'hash-hc',
        'account_type' => 'hardcore_ironman',
    ]);
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-hc', 'ExHardcore', 'ironman'))
        ->assertSuccessful();

    expect($account->fresh()->account_type->value)->toBe('ironman');
});
