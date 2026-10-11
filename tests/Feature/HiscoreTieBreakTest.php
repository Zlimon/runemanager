<?php

use App\Models\Account;
use App\Models\AccountCombatAchievement;
use App\Models\AccountHiscore;
use App\Models\Loot;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

/**
 * A board whose rows all tie has no order of its own, so without a final
 * tie-break it keeps whatever order the database returned — and that order is
 * not stable between requests. Page 1 and page 2 are separate requests, so
 * such a board could show one account twice and drop another, and a stamped
 * rank could move under the reader between page loads.
 *
 * Every board below is seeded in reverse alphabetical order, so a board that
 * fell back to insertion order would fail these assertions rather than pass
 * them by luck: SQLite returns a stable order, so only the seeding order makes
 * the missing tie-break visible here.
 */
beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
    Loot::query()->delete();
});

/**
 * Thirty accounts tied on every metric, created Player30 first, as a fresh
 * instance looks before anyone has played.
 *
 * @return array<int, string> the usernames, in the order the board should show them
 */
function tiedSkillBoard(User $user): array
{
    foreach (range(30, 1) as $position) {
        AccountHiscore::create([
            'account_id' => Account::factory()->for($user)->create(['username' => sprintf('Player%02d', $position)])->id,
            'entries' => ['skills' => ['attack' => ['rank' => 0, 'level' => 1, 'xp' => 0]], 'activities' => []],
            'fetched_at' => now(),
        ]);
    }

    return array_map(fn (int $position): string => sprintf('Player%02d', $position), range(1, 30));
}

/**
 * @return array<int, string>
 */
function usernamesOn(string $url): array
{
    $names = [];

    test()->actingAs(test()->user)
        ->get($url)
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) use (&$names) {
            $names = array_column(array_column($page->toArray()['props']['hiscores']['data'], 'account'), 'username');
        });

    return $names;
}

it('breaks a fully tied skill board by username', function () {
    $expected = tiedSkillBoard($this->user);

    expect(usernamesOn(route('hiscores.skills.index', 'attack')))
        ->toBe(array_slice($expected, 0, 25));
});

it('covers every account exactly once across the pages of a tied board', function () {
    $expected = tiedSkillBoard($this->user);

    $seen = [
        ...usernamesOn(route('hiscores.skills.index', 'attack')),
        ...usernamesOn(route('hiscores.skills.index', ['skill' => 'attack', 'page' => 2])),
    ];

    // No row shown twice, none dropped — the two pages partition the board.
    expect($seen)->toBe($expected)
        ->and(array_unique($seen))->toHaveCount(30);
});

it('breaks a fully tied activity board by username', function () {
    foreach (range(30, 1) as $position) {
        AccountHiscore::create([
            'account_id' => Account::factory()->for($this->user)->create(['username' => sprintf('Player%02d', $position)])->id,
            'entries' => ['skills' => [], 'activities' => ['zulrah' => ['rank' => 0, 'score' => 0]]],
            'fetched_at' => now(),
        ]);
    }

    $seen = [
        ...usernamesOn(route('hiscores.bosses.index', 'zulrah')),
        ...usernamesOn(route('hiscores.bosses.index', ['collection' => 'zulrah', 'page' => 2])),
    ];

    expect($seen)->toBe(array_map(fn (int $p): string => sprintf('Player%02d', $p), range(1, 30)));
});

it('gives a tied stamped board the same ranks on every request', function () {
    foreach (range(30, 1) as $position) {
        $account = Account::factory()->for($this->user)->create(['username' => sprintf('Player%02d', $position)]);
        // Everyone on zero points, as they are before anyone plays.
        AccountCombatAchievement::factory()->for($account)->create(['points' => 0, 'tiers' => []]);
    }

    $rankOf = function (string $username): int {
        $rank = null;

        $this->actingAs($this->user)
            ->get(route('hiscores.combat-achievements.index', ['username' => $username]))
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use (&$rank) {
                $rank = $page->toArray()['props']['hiscores']['data'][0]['rank'];
            });

        return $rank;
    };

    // Two separate requests for the same player must agree, and the stamped
    // rank must match the alphabetical position the tie-break gives them.
    expect($rankOf('Player07'))->toBe(7)
        ->and($rankOf('Player07'))->toBe(7)
        ->and($rankOf('Player30'))->toBe(30);

    $seen = [
        ...usernamesOn(route('hiscores.combat-achievements.index')),
        ...usernamesOn(route('hiscores.combat-achievements.index', ['page' => 2])),
    ];

    expect($seen)->toBe(array_map(fn (int $p): string => sprintf('Player%02d', $p), range(1, 30)));
});

it('breaks a tied loot board by username rather than by Mongo order', function () {
    foreach (range(30, 1) as $position) {
        $account = Account::factory()->for($this->user)->create(['username' => sprintf('Player%02d', $position)]);

        Loot::create([
            'account_id' => $account->id,
            'source' => 'Vorkath',
            'type' => 'NPC',
            'items' => [],
            // Identical totals, so only the tie-break decides the order.
            'total_value' => 1_000,
            'killed_at' => now(),
        ]);
    }

    $seen = [
        ...usernamesOn(route('hiscores.loot.show', ['type' => 'npc', 'source' => 'Vorkath'])),
        ...usernamesOn(route('hiscores.loot.show', ['type' => 'npc', 'source' => 'Vorkath', 'page' => 2])),
    ];

    expect($seen)->toBe(array_map(fn (int $p): string => sprintf('Player%02d', $p), range(1, 30)));

    Loot::query()->delete();
});
