<?php

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\Loot;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
    Loot::query()->delete();
});

/**
 * Thirty accounts named Player01…Player30, each holding the Attack rank that
 * matches its number, so a row's expected rank is readable from its username.
 * Thirty is more than one 25-row page, so the last five rows are only ever
 * reachable through page 2 or a search.
 *
 * @return array<int, Account>
 */
function paginationBoard(User $user): array
{
    $accounts = [];

    foreach (range(1, 30) as $position) {
        $account = Account::factory()->for($user)->create([
            'username' => sprintf('Player%02d', $position),
        ]);

        AccountHiscore::create([
            'account_id' => $account->id,
            'entries' => ['skills' => ['attack' => ['rank' => $position, 'level' => 99, 'xp' => 13_034_431]], 'activities' => []],
            'fetched_at' => now(),
        ]);

        $accounts[] = $account;
    }

    return $accounts;
}

it('pages a board at 25 rows and reports the filter state', function () {
    paginationBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', 'attack'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Skills/Show')
            ->has('hiscores.data', 25)
            ->has('hiscores.links')
            ->where('hiscores.meta.current_page', 1)
            ->where('hiscores.meta.last_page', 2)
            ->where('hiscores.meta.per_page', 25)
            ->where('hiscores.meta.total', 30)
            ->has('hiscores.meta.links')
            ->where('hiscores.data.0.account.username', 'Player01')
            ->where('hiscores.data.24.account.username', 'Player25')
            ->has('filters', fn ($filters) => $filters
                ->where('username', '')
                ->where('per_page', 25)
                ->where('sort', null)
                ->where('direction', null)
                ->where('sortable', ['rank', 'level', 'xp']),
            ),
        );
});

it('continues the board on page 2 without restarting the ranks', function () {
    paginationBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'page' => 2]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 5)
            ->where('hiscores.meta.current_page', 2)
            ->where('hiscores.meta.from', 26)
            ->where('hiscores.data.0.account.username', 'Player26')
            // The rank is the one earned against all 30 accounts, not the
            // row's offset within the page.
            ->where('hiscores.data.0.rank', 26)
            ->where('hiscores.data.4.account.username', 'Player30')
            ->where('hiscores.data.4.rank', 30),
        );
});

it('finds a player the first page does not show', function () {
    paginationBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'username' => 'Player30']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 1)
            ->where('hiscores.meta.total', 1)
            ->where('hiscores.data.0.account.username', 'Player30')
            // Filtering narrows what is shown, never how it is ranked.
            ->where('hiscores.data.0.rank', 30)
            ->where('filters.username', 'Player30'),
        );
});

it('matches usernames case-insensitively on a substring, like the account index', function () {
    paginationBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'username' => 'player2']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            // Player20…Player29, all found from a lowercase term.
            ->has('hiscores.data', 10)
            ->where('hiscores.data.0.account.username', 'Player20')
            ->where('hiscores.data.9.account.username', 'Player29'),
        );
});

it('keeps the search on the pagination links', function () {
    paginationBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'username' => 'Player', 'per_page' => 10]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 10)
            ->where('hiscores.meta.last_page', 3)
            ->where('filters.per_page', 10)
            ->where('hiscores.links.next', function (string $url): bool {
                parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);

                return $query['username'] === 'Player'
                    && $query['per_page'] === '10'
                    && $query['page'] === '2';
            }),
        );
});

it('renders an empty page past the end of the board', function () {
    paginationBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'page' => 99]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 0)
            ->where('hiscores.meta.current_page', 99)
            ->where('hiscores.meta.last_page', 2)
            ->where('hiscores.meta.total', 30),
        );
});

it('renders an empty page when nothing matches the search', function () {
    paginationBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'username' => 'Nobody']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 0)
            ->where('hiscores.meta.total', 0)
            ->where('filters.username', 'Nobody'),
        );
});

it('rejects a search term that is not a valid username', function () {
    paginationBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.skills.index', ['skill' => 'attack', 'username' => 'way too long a term']))
        ->assertSessionHasErrors('username');
});

it('pages the overall, diary, combat achievement and collection log boards too', function () {
    paginationBoard($this->user);

    $this->actingAs($this->user)
        ->get(route('hiscores.overall.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 5)
            ->where('hiscores.meta.total', 30)
            ->has('filters'),
        );

    foreach (['hiscores.diaries.index', 'hiscores.combat-achievements.index', 'hiscores.collection-log.index'] as $name) {
        $this->actingAs($this->user)
            ->get(route($name))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('hiscores.data')
                ->where('hiscores.meta.per_page', 25)
                ->has('filters'),
            );
    }
});

it('pages a boss board and keeps the OSRS rank across pages', function () {
    foreach (range(1, 30) as $position) {
        $account = Account::factory()->for($this->user)->create([
            'username' => sprintf('Slayer%02d', $position),
        ]);

        AccountHiscore::create([
            'account_id' => $account->id,
            'entries' => ['skills' => [], 'activities' => ['zulrah' => ['rank' => $position, 'score' => 1_000 - $position]]],
            'fetched_at' => now(),
        ]);
    }

    $this->actingAs($this->user)
        ->get(route('hiscores.bosses.index', ['collection' => 'zulrah', 'page' => 2]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Bosses/Show')
            ->where('collectionSlug', 'zulrah')
            ->has('hiscores.data', 5)
            ->where('hiscores.data.0.account.username', 'Slayer26')
            ->where('hiscores.data.0.rank', 26),
        );

    $this->actingAs($this->user)
        ->get(route('hiscores.bosses.index', ['collection' => 'zulrah', 'username' => 'Slayer30']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 1)
            ->where('hiscores.data.0.rank', 30),
        );
});

it('keeps a loot board position the same on page 2 and under a search', function () {
    foreach (range(1, 30) as $position) {
        $account = Account::factory()->for($this->user)->create([
            'username' => sprintf('Looter%02d', $position),
        ]);

        Loot::create([
            'account_id' => $account->id,
            'source' => 'Vorkath',
            'type' => 'NPC',
            'items' => [['id' => 536, 'quantity' => 1]],
            // Descending value, so Looter01 is first and Looter30 is 30th.
            'total_value' => (31 - $position) * 1_000,
            'killed_at' => now(),
        ]);
    }

    $this->actingAs($this->user)
        ->get(route('hiscores.loot.show', ['type' => 'npc', 'source' => 'Vorkath', 'page' => 2]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Loot/Show')
            ->has('hiscores.data', 5)
            ->where('hiscores.meta.total', 30)
            ->where('hiscores.data.0.account.username', 'Looter26')
            ->where('hiscores.data.0.rank', 26),
        );

    // The same player, found by search from page 1, carries the same position.
    $this->actingAs($this->user)
        ->get(route('hiscores.loot.show', ['type' => 'npc', 'source' => 'Vorkath', 'username' => 'Looter26']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 1)
            ->where('hiscores.data.0.rank', 26)
            ->where('filters.username', 'Looter26'),
        );

    Loot::query()->delete();
});

it('still 404s a loot source with no loot, but renders one whose search matches nobody', function () {
    $account = Account::factory()->for($this->user)->create(['username' => 'Habski']);

    Loot::create([
        'account_id' => $account->id,
        'source' => 'Vorkath',
        'type' => 'NPC',
        'items' => [],
        'total_value' => 100,
        'killed_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->get(route('hiscores.loot.show', ['type' => 'npc', 'source' => 'Nonexistent']))
        ->assertNotFound();

    $this->actingAs($this->user)
        ->get(route('hiscores.loot.show', ['type' => 'npc', 'source' => 'Vorkath', 'username' => 'Nobody']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('hiscores.data', 0)
            ->where('hiscores.meta.total', 0),
        );

    Loot::query()->delete();
});
