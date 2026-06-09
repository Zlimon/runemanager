<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Services\Accounts\GroupIronmanValidator;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function gimHeaders(string $hash, string $username): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

function stubGim(bool $result): void
{
    test()->mock(GroupIronmanValidator::class, function ($mock) use ($result) {
        $mock->shouldReceive('isGroupIronman')->andReturn($result);
    });
}

beforeEach(fn () => SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP));

it('creates and links a GIM account on first plugin login in group mode', function () {
    stubGim(true);
    $user = User::factory()->withPersonalTeam()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-g', 'GimGuy'))
        ->assertSuccessful();

    $account = Account::where('username', 'GimGuy')->firstOrFail();
    expect($account->user_id)->toBe($user->id);
    expect($account->account_type->value)->toBe('group_ironman');
});

it('rejects a non-GIM account on plugin login in group mode', function () {
    stubGim(false);
    Sanctum::actingAs(User::factory()->withPersonalTeam()->create());

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-m', 'MainGuy'))
        ->assertForbidden();

    expect(Account::where('username', 'MainGuy')->exists())->toBeFalse();
});

it('rejects claiming an unclaimed account that is not a GIM', function () {
    stubGim(false);
    $account = Account::factory()->create(['username' => 'Rostered', 'user_id' => null, 'account_hash' => null]);
    Sanctum::actingAs(User::factory()->withPersonalTeam()->create());

    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-r', 'Rostered'))
        ->assertForbidden();

    expect($account->fresh()->user_id)->toBeNull();
});

it('does not validate account type in casual mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);
    $user = User::factory()->withPersonalTeam()->create();
    Sanctum::actingAs($user);

    // No GIM stub — casual mode must not call the validator at all.
    $this->putJson('/api/plugin/heartbeat', [], gimHeaders('hash-c', 'CasualGuy'))
        ->assertSuccessful();

    expect(Account::where('username', 'CasualGuy')->first()->account_type->value)->toBe('normal');
});
