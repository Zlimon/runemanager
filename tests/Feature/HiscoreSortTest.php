<?php

use App\Models\Account;
use App\Models\AccountCombatAchievement;
use App\Models\AccountDiary;
use App\Models\AccountHiscore;
use App\Models\CollectionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
});

/**
 * Four Attack rows whose rank order, level order and XP order all differ, so a
 * sort that silently fell back to the canonical order would be visible. Bea and
 * Dot tie on both level and XP, which pins down the tie-break too.
 */
function sortableSkillBoard(User $user): void
{
    $rows = [
        // username, rank, level, xp
        ['Ace', 1, 70, 800_000],
        ['Bea', 2, 99, 13_034_431],
        ['Cid', 3, 80, 2_000_000],
        ['Dot', 0, 99, 13_034_431], // never on the hiscores
    ];

    foreach ($rows as [$username, $rank, $level, $xp]) {
        AccountHiscore::create([
            'account_id' => Account::factory()->for($user)->create(['username' => $username])->id,
            'entries' => ['skills' => ['attack' => ['rank' => $rank, 'level' => $level, 'xp' => $xp]], 'activities' => []],
            'fetched_at' => now(),
        ]);
    }
}

/**
 * @param  array<int, string>  $usernames
 */
function assertSkillBoardOrder(array $query, array $usernames): void
{
    test()->actingAs(test()->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', ...$query]))
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) use ($usernames) {
            $page->has('hiscores.data', count($usernames));

            foreach ($usernames as $index => $username) {
                $page->where("hiscores.data.{$index}.account.username", $username);
            }

            // Whatever the order, a player carries the rank the canonical
            // board gave them: Ace 1st, Bea 2nd, Cid 3rd, Dot unranked.
            foreach (['Ace' => 1, 'Bea' => 2, 'Cid' => 3, 'Dot' => 0] as $username => $rank) {
                $page->where('hiscores.data.'.array_search($username, $usernames, true).'.rank', $rank);
            }
        });
}

it('leaves an unsorted board in its canonical rank order', function () {
    sortableSkillBoard($this->user);

    assertSkillBoardOrder([], ['Ace', 'Bea', 'Cid', 'Dot']);
});

it('sorts by XP in both directions without touching the ranks', function () {
    sortableSkillBoard($this->user);

    // Bea and Dot tie on XP, so the canonical order decides between them.
    assertSkillBoardOrder(['sort' => 'xp', 'direction' => 'desc'], ['Bea', 'Dot', 'Cid', 'Ace']);
    assertSkillBoardOrder(['sort' => 'xp', 'direction' => 'asc'], ['Ace', 'Cid', 'Bea', 'Dot']);
});

it('sorts by level in both directions without touching the ranks', function () {
    sortableSkillBoard($this->user);

    assertSkillBoardOrder(['sort' => 'level', 'direction' => 'desc'], ['Bea', 'Dot', 'Cid', 'Ace']);
    assertSkillBoardOrder(['sort' => 'level', 'direction' => 'asc'], ['Ace', 'Cid', 'Bea', 'Dot']);
});

it('reads a metric column best-first when no direction is given', function () {
    sortableSkillBoard($this->user);

    assertSkillBoardOrder(['sort' => 'xp'], ['Bea', 'Dot', 'Cid', 'Ace']);
});

it('reproduces the canonical order when sorting by rank ascending', function () {
    sortableSkillBoard($this->user);

    assertSkillBoardOrder(['sort' => 'rank'], ['Ace', 'Bea', 'Cid', 'Dot']);
    assertSkillBoardOrder(['sort' => 'rank', 'direction' => 'asc'], ['Ace', 'Bea', 'Cid', 'Dot']);
});

it('puts the unranked first when sorting by rank worst-first', function () {
    sortableSkillBoard($this->user);

    assertSkillBoardOrder(['sort' => 'rank', 'direction' => 'desc'], ['Dot', 'Cid', 'Bea', 'Ace']);
});

it('reports the sort state and the board allow-list in the filters prop', function () {
    sortableSkillBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'sort' => 'xp']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('filters', fn ($filters) => $filters
                ->where('username', '')
                ->where('per_page', 25)
                ->where('sort', 'xp')
                // Filled in from the column, since the request gave none.
                ->where('direction', 'desc')
                ->where('sortable', ['rank', 'level', 'xp']),
            ),
        );
});

it('leaves sort and direction null while the board is in its canonical order', function () {
    sortableSkillBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', 'attack'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.sort', null)
            ->where('filters.direction', null),
        );
});

it('rejects a column the board does not render', function () {
    sortableSkillBoard($this->user);

    // `kill_count` is sortable on an activity board, but the skill board has
    // no such column, so the allow-list is per board and not global.
    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'sort' => 'kill_count']))
        ->assertSessionHasErrors('sort');

    $this->actingAs($this->user)
        ->getJson(route('hiscores.skills.index', ['skill' => 'attack', 'sort' => 'account']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('sort');
});

it('rejects a direction that is neither asc nor desc', function () {
    sortableSkillBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'sort' => 'xp', 'direction' => 'sideways']))
        ->assertSessionHasErrors('direction');

    $this->actingAs($this->user)
        ->getJson(route('hiscores.skills.index', ['skill' => 'attack', 'sort' => 'xp', 'direction' => 'sideways']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('direction');
});

it('starts a newly sorted board on page 1 and carries the sort onto its links', function () {
    foreach (range(1, 30) as $position) {
        AccountHiscore::create([
            'account_id' => Account::factory()->for($this->user)->create(['username' => sprintf('Player%02d', $position)])->id,
            'entries' => ['skills' => ['attack' => ['rank' => $position, 'level' => $position, 'xp' => $position * 1_000]], 'activities' => []],
            'fetched_at' => now(),
        ]);
    }

    // The frontend drops `page` when the sort changes; the paginator must not
    // put a stale one back through withQueryString().
    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'sort' => 'xp', 'direction' => 'desc']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.meta.current_page', 1)
            ->where('hiscores.data.0.account.username', 'Player30')
            ->where('hiscores.links.next', function (string $url): bool {
                parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);

                return $query['sort'] === 'xp'
                    && $query['direction'] === 'desc'
                    && $query['page'] === '2';
            }),
        );

    // Page 2 of the sorted board continues it, ranks intact.
    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'sort' => 'xp', 'direction' => 'desc', 'page' => 2]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 5)
            ->where('hiscores.data.0.account.username', 'Player05')
            ->where('hiscores.data.0.rank', 5),
        );
});

it('sorts the diaries board by any of its tier columns', function () {
    $allRounder = Account::factory()->for($this->user)->create(['username' => 'AllRounder']);
    $eliteOnly = Account::factory()->for($this->user)->create(['username' => 'EliteOnly']);

    // AllRounder completes more tiers overall; EliteOnly completes more elites.
    AccountDiary::create(['account_id' => $allRounder->id, 'diaries' => [
        'Varrock' => ['Easy' => true, 'Medium' => true, 'Hard' => true],
    ]]);
    AccountDiary::create(['account_id' => $eliteOnly->id, 'diaries' => [
        'Karamja' => ['Elite' => true],
        'Ardougne' => ['Elite' => true],
    ]]);

    $this->actingAs($this->user)
        ->get(route('hiscores.diaries.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.data.0.account.username', 'AllRounder')
            ->where('filters.sortable', ['rank', 'completed', 'easy', 'medium', 'hard', 'elite']),
        );

    $this->actingAs($this->user)
        ->get(route('hiscores.diaries.index', ['sort' => 'elite']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.data.0.account.username', 'EliteOnly')
            ->where('hiscores.data.0.elite', 2)
            ->where('hiscores.data.1.account.username', 'AllRounder'),
        );

    $this->actingAs($this->user)
        ->get(route('hiscores.diaries.index', ['sort' => 'hard', 'direction' => 'desc']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.data.0.account.username', 'AllRounder'),
        );
});

it('sorts the combat achievements board by tasks as well as points', function () {
    $pointy = Account::factory()->for($this->user)->create(['username' => 'Pointy']);
    $busy = Account::factory()->for($this->user)->create(['username' => 'Busy']);

    // Pointy has the points; Busy has the task count.
    AccountCombatAchievement::factory()->for($pointy)->create(['points' => 1500, 'tiers' => ['easy' => 5]]);
    AccountCombatAchievement::factory()->for($busy)->create(['points' => 40, 'tiers' => ['easy' => 41, 'medium' => 60]]);

    $this->actingAs($this->user)
        ->get(route('hiscores.combat-achievements.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.data.0.account.username', 'Pointy')
            ->where('filters.sortable', ['rank', 'points', 'tasks_completed']),
        );

    $this->actingAs($this->user)
        ->get(route('hiscores.combat-achievements.index', ['sort' => 'tasks_completed']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.data.0.account.username', 'Busy')
            ->where('hiscores.data.0.tasks_completed', 101)
            // The points column is untouched by a sort on another column.
            ->where('hiscores.data.0.points', 40),
        );
});

/**
 * The diaries, combat achievement and collection log boards have no OSRS rank
 * to show, so the controllers stamp each row with its standing over the whole
 * instance. That number has to survive a search the same way an OSRS rank does.
 */
it('keeps the stamped rank on the diaries board through a search', function () {
    foreach ([['Alpha', 3], ['Bravo', 2], ['Charlie', 1]] as [$username, $tiers]) {
        $account = Account::factory()->for($this->user)->create(['username' => $username]);
        AccountDiary::create(['account_id' => $account->id, 'diaries' => [
            'Varrock' => array_slice(['Easy' => true, 'Medium' => true, 'Hard' => true], 0, $tiers),
        ]]);
    }

    $this->actingAs($this->user)
        ->get(route('hiscores.diaries.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.data.0.account.username', 'Alpha')
            ->where('hiscores.data.0.rank', 1)
            ->where('hiscores.data.1.rank', 2)
            ->where('hiscores.data.2.account.username', 'Charlie')
            ->where('hiscores.data.2.rank', 3),
        );

    // Charlie alone on the page, still 3rd in the instance — not 1st.
    $this->actingAs($this->user)
        ->get(route('hiscores.diaries.index', ['username' => 'Charlie']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 1)
            ->where('hiscores.data.0.rank', 3),
        );
});

it('stamps a rank on the combat achievement and collection log boards', function () {
    $top = Account::factory()->for($this->user)->create(['username' => 'Topper']);
    $low = Account::factory()->for($this->user)->create(['username' => 'Lower']);

    AccountCombatAchievement::factory()->for($top)->create(['points' => 1500, 'tiers' => ['easy' => 41]]);
    AccountCombatAchievement::factory()->for($low)->create(['points' => 40, 'tiers' => ['easy' => 1]]);

    $this->actingAs($this->user)
        ->get(route('hiscores.combat-achievements.index', ['username' => 'Lower']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 1)
            ->where('hiscores.data.0.account.username', 'Lower')
            ->where('hiscores.data.0.rank', 2)
            ->where('filters.sortable', ['rank', 'points', 'tasks_completed']),
        );

    CollectionLog::create(['account_id' => $top->id, 'obtained' => 1200, 'total' => 1500]);
    CollectionLog::create(['account_id' => $low->id, 'obtained' => 12, 'total' => 1500]);

    $this->actingAs($this->user)
        ->get(route('hiscores.collection-log.index', ['username' => 'Lower']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 1)
            ->where('hiscores.data.0.rank', 2)
            ->where('filters.sortable', ['rank', 'obtained', 'total']),
        );
});

it('sorts a stamped board by rank without renumbering it', function () {
    foreach ([['Alpha', 1500], ['Bravo', 900], ['Charlie', 40]] as [$username, $points]) {
        $account = Account::factory()->for($this->user)->create(['username' => $username]);
        AccountCombatAchievement::factory()->for($account)->create(['points' => $points, 'tiers' => ['easy' => 1]]);
    }

    // Worst-first by rank reverses the board; the numbers travel with the rows.
    $this->actingAs($this->user)
        ->get(route('hiscores.combat-achievements.index', ['sort' => 'rank', 'direction' => 'desc']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hiscores.data.0.account.username', 'Charlie')
            ->where('hiscores.data.0.rank', 3)
            ->where('hiscores.data.2.account.username', 'Alpha')
            ->where('hiscores.data.2.rank', 1),
        );
});

it('sorts within the search results, over the whole board', function () {
    sortableSkillBoard($this->user);

    // Only Ace and Bea match. Canonically Ace leads on rank; by XP, Bea does —
    // and both keep the rank the whole board gave them.
    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'username' => 'a', 'sort' => 'xp', 'direction' => 'desc']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 2)
            ->where('hiscores.data.0.account.username', 'Bea')
            ->where('hiscores.data.0.rank', 2)
            ->where('hiscores.data.1.account.username', 'Ace')
            ->where('hiscores.data.1.rank', 1),
        );
});
