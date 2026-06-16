<?php

use App\Events\AccountDataUpdated;
use App\Events\FeedEventCreated;
use App\Models\Account;
use App\Models\AccountCombatAchievement;
use App\Models\FeedEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function caHeaders(string $hash = 'hash-ca', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

function caAccount(): Account
{
    $user = User::factory()->withPersonalTeam()->create();

    return Account::factory()->for($user)->create([
        'username' => 'Zlimon',
        'account_hash' => 'hash-ca',
    ]);
}

it('stores and normalises the combat achievement snapshot and broadcasts', function () {
    Event::fake([AccountDataUpdated::class]);
    $account = caAccount();
    Sanctum::actingAs($account->user);

    $this->putJson('/api/plugin/combat-achievements', [
        'points' => 420,
        'tiers' => [
            'easy' => 27,
            'medium' => 29,
            'bogus' => 5,  // unknown tier — dropped
            'hard' => -3,  // clamped to 0
        ],
    ], caHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.points', 420)
        ->assertJsonPath('data.tasks_completed', 56);

    $ca = AccountCombatAchievement::where('account_id', $account->id)->firstOrFail();
    expect($ca->points)->toBe(420)
        ->and($ca->tiers['easy'])->toBe(27)
        ->and($ca->tiers['medium'])->toBe(29)
        ->and($ca->tiers['hard'])->toBe(0)
        ->and($ca->tiers['grandmaster'])->toBe(0)
        ->and($ca->tiers)->not->toHaveKey('bogus');

    Event::assertDispatched(AccountDataUpdated::class,
        fn (AccountDataUpdated $e) => $e->type === 'combat_achievements' && $e->account->is($account));
});

it('upserts on repeat snapshots', function () {
    $account = caAccount();
    Sanctum::actingAs($account->user);

    $this->putJson('/api/plugin/combat-achievements', ['points' => 10, 'tiers' => ['easy' => 5]], caHeaders())->assertSuccessful();
    $this->putJson('/api/plugin/combat-achievements', ['points' => 25, 'tiers' => ['easy' => 12]], caHeaders())->assertSuccessful();

    expect(AccountCombatAchievement::where('account_id', $account->id)->count())->toBe(1);
    $ca = AccountCombatAchievement::where('account_id', $account->id)->firstOrFail();
    expect($ca->points)->toBe(25)->and($ca->tiers['easy'])->toBe(12);
});

it('records a feed event on a task unlock', function () {
    Event::fake([FeedEventCreated::class]);
    $account = caAccount();
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/combat-achievements/unlock', [
        'task' => "Ghommal's Hilt 6",
        'tier' => 'grandmaster',
    ], caHeaders())->assertSuccessful();

    $event = FeedEvent::where('account_id', $account->id)->firstOrFail();
    expect($event->type)->toBe(FeedEvent::TYPE_COMBAT_ACHIEVEMENT)
        ->and($event->payload['task'])->toBe("Ghommal's Hilt 6")
        ->and($event->payload['tier'])->toBe('grandmaster');

    Event::assertDispatched(FeedEventCreated::class);
});

it('records a feed event for a tierless (popup) unlock', function () {
    $account = caAccount();
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/combat-achievements/unlock', ['task' => 'Some Task'], caHeaders())
        ->assertSuccessful();

    $event = FeedEvent::where('account_id', $account->id)->firstOrFail();
    expect($event->type)->toBe(FeedEvent::TYPE_COMBAT_ACHIEVEMENT)
        ->and($event->payload['task'])->toBe('Some Task')
        ->and($event->payload['tier'])->toBeNull();
});

it('rejects an unlock with an unknown tier', function () {
    $account = caAccount();
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/combat-achievements/unlock', ['task' => 'X', 'tier' => 'impossible'], caHeaders())
        ->assertStatus(422)
        ->assertJsonValidationErrors('tier');
});

it('requires authentication', function () {
    $this->putJson('/api/plugin/combat-achievements', ['points' => 0, 'tiers' => []], ['Accept' => 'application/json'])
        ->assertUnauthorized();
});

it('exposes combat achievements on the account show page', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create(['username' => 'Achiever']);
    AccountCombatAchievement::factory()->for($account)->create([
        'points' => 333,
        'tiers' => ['easy' => 41],
    ]);

    $this->actingAs($user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('combatAchievements.points', 333)
            ->where('combatAchievements.tiers.easy', 41),
        );
});

it('ranks the combat achievements leaderboard by points', function () {
    $most = Account::factory()->for(User::factory()->withPersonalTeam())->create(['username' => 'Maxed']);
    $few = Account::factory()->for(User::factory()->withPersonalTeam())->create(['username' => 'Starter']);

    AccountCombatAchievement::factory()->for($most)->create(['points' => 1500, 'tiers' => ['easy' => 41, 'medium' => 60]]);
    AccountCombatAchievement::factory()->for($few)->create(['points' => 40, 'tiers' => ['easy' => 10]]);

    $this->actingAs($most->user)
        ->get(route('hiscores.combat-achievements.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/CombatAchievements/Show')
            ->has('hiscores', 2)
            ->where('hiscores.0.account.username', 'Maxed')
            ->where('hiscores.0.points', 1500)
            ->where('hiscores.0.tasks_completed', 101)
            ->where('hiscores.1.account.username', 'Starter')
            ->where('hiscores.1.points', 40),
        );
});
