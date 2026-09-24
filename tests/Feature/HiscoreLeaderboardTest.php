<?php

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/**
 * @param  array<string, array<string, int>>  $skills
 * @param  array<string, array<string, int>>  $activities
 */
function leaderboardHiscore(Account $account, array $skills = [], array $activities = []): void
{
    AccountHiscore::create([
        'account_id' => $account->id,
        'entries' => ['skills' => $skills, 'activities' => $activities],
        'fetched_at' => now(),
    ]);
}

it('ranks a skill board by rank with unranked accounts last, then level and XP', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $unranked = Account::factory()->for($user)->create(['username' => 'Unranked']);
    $lowXp = Account::factory()->for($user)->create(['username' => 'LowXp']);
    $highXp = Account::factory()->for($user)->create(['username' => 'HighXp']);
    $highLevel = Account::factory()->for($user)->create(['username' => 'HighLevel']);
    $best = Account::factory()->for($user)->create(['username' => 'Best']);
    $missing = Account::factory()->for($user)->create(['username' => 'Missing']);

    leaderboardHiscore($unranked, ['attack' => ['rank' => 0, 'level' => 99, 'xp' => 13_034_431]]);
    leaderboardHiscore($lowXp, ['attack' => ['rank' => 500, 'level' => 80, 'xp' => 2_000_000]]);
    leaderboardHiscore($highXp, ['attack' => ['rank' => 500, 'level' => 80, 'xp' => 2_100_000]]);
    leaderboardHiscore($highLevel, ['attack' => ['rank' => 500, 'level' => 85, 'xp' => 1_000_000]]);
    leaderboardHiscore($best, ['attack' => ['rank' => 10, 'level' => 70, 'xp' => 800_000]]);
    leaderboardHiscore($missing);

    $this->actingAs($user)
        ->get(route('hiscores.skills.index', 'attack'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Skills/Show')
            ->where('skillName', 'Attack')
            ->where('skillSlug', 'attack')
            ->has('hiscores.data', 6)
            ->where('hiscores.data.0.account.username', 'Best')
            ->where('hiscores.data.1.account.username', 'HighLevel')
            ->where('hiscores.data.2.account.username', 'HighXp')
            ->where('hiscores.data.3.account.username', 'LowXp')
            ->where('hiscores.data.3.rank', 500)
            ->where('hiscores.data.3.level', 80)
            ->where('hiscores.data.3.xp', 2000000)
            // Both unranked accounts sort after every ranked one, higher level first.
            ->where('hiscores.data.4.account.username', 'Unranked')
            ->where('hiscores.data.5.account.username', 'Missing')
            // A skill missing from the entries defaults to level 1.
            ->where('hiscores.data.5.rank', 0)
            ->where('hiscores.data.5.level', 1)
            ->where('hiscores.data.5.xp', 0),
        );
});

it('defaults a missing overall entry to level 0', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create(['username' => 'Missing']);
    leaderboardHiscore($account);

    $this->actingAs($user)
        ->get(route('hiscores.overall.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Skills/Show')
            ->has('hiscores.data', 1)
            ->where('hiscores.data.0.rank', 0)
            ->where('hiscores.data.0.level', 0)
            ->where('hiscores.data.0.xp', 0),
        );
});

it('returns 404 for an unknown skill', function () {
    $user = User::factory()->withPersonalTeam()->create();

    $this->actingAs($user)
        ->get(route('hiscores.skills.index', 'not-a-skill'))
        ->assertNotFound();
});

it('ranks a boss board by rank with unranked accounts last, then kill count', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $unranked = Account::factory()->for($user)->create(['username' => 'Unranked']);
    $fewer = Account::factory()->for($user)->create(['username' => 'Fewer']);
    $more = Account::factory()->for($user)->create(['username' => 'More']);
    $best = Account::factory()->for($user)->create(['username' => 'Best']);
    $missing = Account::factory()->for($user)->create(['username' => 'Missing']);

    leaderboardHiscore($unranked, activities: ['general_graardor' => ['rank' => 0, 'score' => 9_000]]);
    leaderboardHiscore($fewer, activities: ['general_graardor' => ['rank' => 700, 'score' => 50]]);
    leaderboardHiscore($more, activities: ['general_graardor' => ['rank' => 700, 'score' => 60]]);
    leaderboardHiscore($best, activities: ['general_graardor' => ['rank' => 20, 'score' => 10]]);
    leaderboardHiscore($missing);

    $this->actingAs($user)
        ->get(route('hiscores.bosses.index', 'general-graardor'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Bosses/Show')
            ->where('collectionName', 'General Graardor')
            ->where('collectionSlug', 'general-graardor')
            ->has('hiscores.data', 5)
            ->where('hiscores.data.0.account.username', 'Best')
            ->where('hiscores.data.1.account.username', 'More')
            ->where('hiscores.data.1.rank', 700)
            ->where('hiscores.data.1.kill_count', 60)
            ->where('hiscores.data.2.account.username', 'Fewer')
            ->where('hiscores.data.3.account.username', 'Unranked')
            ->where('hiscores.data.4.account.username', 'Missing')
            ->where('hiscores.data.4.rank', 0)
            ->where('hiscores.data.4.kill_count', 0),
        );
});

it('ranks a clue board from the clue activity', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $low = Account::factory()->for($user)->create(['username' => 'Low']);
    $high = Account::factory()->for($user)->create(['username' => 'High']);

    leaderboardHiscore($low, activities: ['clue_scrolls_elite' => ['rank' => 900, 'score' => 5]]);
    leaderboardHiscore($high, activities: ['clue_scrolls_elite' => ['rank' => 100, 'score' => 40]]);

    $this->actingAs($user)
        ->get(route('hiscores.clues.index', 'clue-scrolls-elite'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Bosses/Show')
            ->where('collectionName', 'Clue Scrolls Elite')
            ->where('collectionSlug', 'clue-scrolls-elite')
            ->has('hiscores.data', 2)
            ->where('hiscores.data.0.account.username', 'High')
            ->where('hiscores.data.0.kill_count', 40)
            ->where('hiscores.data.1.account.username', 'Low'),
        );
});

it('requires authentication for the skill and activity boards', function () {
    $this->get(route('hiscores.skills.index', 'attack'))->assertRedirect(route('login'));
    $this->get(route('hiscores.bosses.index', 'zulrah'))->assertRedirect(route('login'));
    $this->get(route('hiscores.clues.index', 'clue-scrolls-all'))->assertRedirect(route('login'));
});

it('sorts accounts the OSRS hiscores report as rank -1 after ranked ones on a boss board', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $unranked = Account::factory()->for($user)->create(['username' => 'Unranked']);
    $ranked = Account::factory()->for($user)->create(['username' => 'Ranked']);

    leaderboardHiscore($unranked, activities: ['zulrah' => ['rank' => -1, 'score' => -1]]);
    leaderboardHiscore($ranked, activities: ['zulrah' => ['rank' => 5_000, 'score' => 50]]);

    $this->actingAs($user)
        ->get(route('hiscores.bosses.index', 'zulrah'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 2)
            ->where('hiscores.data.0.account.username', 'Ranked')
            ->where('hiscores.data.1.account.username', 'Unranked')
            ->where('hiscores.data.1.rank', 0)
            ->where('hiscores.data.1.kill_count', 0),
        );
});

it('sorts accounts the OSRS hiscores report as rank -1 after ranked ones on a clue board', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $unranked = Account::factory()->for($user)->create(['username' => 'Unranked']);
    $ranked = Account::factory()->for($user)->create(['username' => 'Ranked']);

    leaderboardHiscore($unranked, activities: ['clue_scrolls_elite' => ['rank' => -1, 'score' => -1]]);
    leaderboardHiscore($ranked, activities: ['clue_scrolls_elite' => ['rank' => 900, 'score' => 5]]);

    $this->actingAs($user)
        ->get(route('hiscores.clues.index', 'clue-scrolls-elite'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.data.0.account.username', 'Ranked')
            ->where('hiscores.data.1.account.username', 'Unranked')
            ->where('hiscores.data.1.kill_count', 0),
        );
});

it('sorts accounts the OSRS hiscores report as rank -1 after ranked ones on a skill board', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $unranked = Account::factory()->for($user)->create(['username' => 'Unranked']);
    $ranked = Account::factory()->for($user)->create(['username' => 'Ranked']);

    leaderboardHiscore($unranked, ['attack' => ['rank' => -1, 'level' => 1, 'xp' => -1]]);
    leaderboardHiscore($ranked, ['attack' => ['rank' => 2_000_000, 'level' => 1, 'xp' => 0]]);

    $this->actingAs($user)
        ->get(route('hiscores.skills.index', 'attack'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.data.0.account.username', 'Ranked')
            ->where('hiscores.data.1.account.username', 'Unranked')
            ->where('hiscores.data.1.rank', 0)
            ->where('hiscores.data.1.xp', 0),
        );
});
