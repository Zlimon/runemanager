<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Monitoring\CommandFailedEvent;
use MongoDB\Driver\Monitoring\CommandStartedEvent;
use MongoDB\Driver\Monitoring\CommandSubscriber;
use MongoDB\Driver\Monitoring\CommandSucceededEvent;

use function MongoDB\Driver\Monitoring\addSubscriber;
use function MongoDB\Driver\Monitoring\removeSubscriber;

uses(RefreshDatabase::class);

/**
 * The SQL run while $work executes. Registering the listener is enough to
 * scope it: every test builds its own application, and with it its own event
 * dispatcher.
 *
 * @return array<int, string>
 */
function sqlDuring(Closure $work): array
{
    $statements = [];

    DB::listen(function ($query) use (&$statements): void {
        $statements[] = $query->sql;
    });

    $work();

    return $statements;
}

/**
 * How many `aggregate` commands the static item collection received while
 * $work ran. Item lookups drop to the raw Mongo driver (see Item), so they
 * never reach DB::listen and have to be counted at the driver.
 */
function itemAggregatesDuring(Closure $work): int
{
    $collection = (new Item)->getTable();

    $subscriber = new class($collection) implements CommandSubscriber
    {
        public int $count = 0;

        public function __construct(private string $collection) {}

        public function commandStarted(CommandStartedEvent $event): void
        {
            if ($event->getCommandName() === 'aggregate' && ($event->getCommand()->aggregate ?? null) === $this->collection) {
                $this->count++;
            }
        }

        public function commandSucceeded(CommandSucceededEvent $event): void {}

        public function commandFailed(CommandFailedEvent $event): void {}
    };

    addSubscriber($subscriber);

    try {
        $work();
    } finally {
        removeSubscriber($subscriber);
    }

    return $subscriber->count;
}

it('memoizes the settings table for a whole request and invalidates it on a write', function () {
    SettingHelper::setSetting('clan_name', 'First');

    $reads = fn (array $sql): int => count(array_filter(
        $sql,
        fn (string $statement): bool => str_starts_with($statement, 'select') && str_contains($statement, '"settings"'),
    ));

    $sql = sqlDuring(function (): void {
        expect(SettingHelper::getSetting('clan_name'))->toBe('First')
            ->and(SettingHelper::getSetting('clan_name'))->toBe('First')
            ->and(SettingHelper::getSetting('clan_name'))->toBe('First');
    });

    expect($reads($sql))->toBe(1);

    // The write drops the memo, so the next read has to go back to the table —
    // otherwise the whole instance would serve the stale value for the request.
    // Two selects: the write reads the row it is about to update (never through
    // the memo, see Setting::set()), then the first read re-primes the memo.
    $sql = sqlDuring(function (): void {
        SettingHelper::setSetting('clan_name', 'Second');

        expect(SettingHelper::getSetting('clan_name'))->toBe('Second')
            ->and(SettingHelper::getSetting('clan_name'))->toBe('Second');
    });

    expect($reads($sql))->toBe(2);
});

it('reads the settings table once across a whole page render', function () {
    $user = User::factory()->withPersonalTeam()->create();
    Account::factory()->count(5)->for($user)->create();

    $sql = sqlDuring(function () use ($user): void {
        $this->actingAs($user)->get(route('accounts.index'))->assertOk();
    });

    $reads = array_filter(
        $sql,
        fn (string $statement): bool => str_starts_with($statement, 'select') && str_contains($statement, '"settings"'),
    );

    expect($reads)->toHaveCount(1);
});

it('does not grow the account index query count with the number of rows', function () {
    $user = User::factory()->withPersonalTeam()->create();
    Account::factory()->count(2)->for($user)->create();

    // One unmeasured render first: the settings memo and the nav caches are
    // warmed by whichever request comes first, and a test reuses one container
    // across its requests where production builds one per request.
    $this->actingAs($user)->get(route('accounts.index'))->assertOk();

    $twoRows = sqlDuring(function () use ($user): void {
        $this->actingAs($user)->get(route('accounts.index'))->assertOk();
    });

    Account::factory()->count(10)->for($user)->create();

    $twelveRows = sqlDuring(function () use ($user): void {
        $this->actingAs($user)->get(route('accounts.index'))->assertOk();
    });

    expect(count($twelveRows))->toBe(count($twoRows));
});

it('resolves every account icon on the index in a single item lookup', function () {
    $user = User::factory()->withPersonalTeam()->create(['icon_id' => 4151]);
    Account::factory()->count(6)->for($user)->create();

    $aggregates = itemAggregatesDuring(function () use ($user): void {
        $this->actingAs($user)->get(route('accounts.index'))->assertOk();
    });

    expect($aggregates)->toBe(1);
});

it('skips the item lookup entirely when no listed account has an icon to resolve', function () {
    $user = User::factory()->withPersonalTeam()->create();
    // Roster placeholders nobody has claimed: no owner, so no icon to look up.
    Account::factory()->count(6)->create(['user_id' => null]);

    $aggregates = itemAggregatesDuring(function () use ($user): void {
        $this->actingAs($user)->get(route('accounts.index'))->assertOk();
    });

    expect($aggregates)->toBe(0);
});

it('short-circuits the icon lookup for a user who picked no icon', function () {
    // Defensive: users.icon_id is NOT NULL today, so this guards the accessor
    // rather than a stored state — lookupByOsrsId(null) would otherwise run a
    // full aggregation matching `id: ''`.
    $icon = null;

    $aggregates = itemAggregatesDuring(function () use (&$icon): void {
        $icon = (new User(['icon_id' => null]))->icon;
    });

    expect($icon)->toBeNull()
        ->and($aggregates)->toBe(0);
});

it('resolves a leaderboard with one item lookup however many rows it has', function () {
    $user = User::factory()->withPersonalTeam()->create(['icon_id' => 4151]);

    foreach (range(1, 6) as $index) {
        AccountHiscore::create([
            'account_id' => Account::factory()->for($user)->create(['username' => "Member{$index}"])->id,
            'entries' => ['skills' => ['attack' => ['rank' => $index, 'level' => 99, 'xp' => 13_034_431]], 'activities' => []],
            'fetched_at' => now(),
        ]);
    }

    $aggregates = itemAggregatesDuring(function () use ($user): void {
        $this->actingAs($user)->get(route('hiscores.skills.index', 'attack'))->assertOk();
    });

    expect($aggregates)->toBe(1);
});

it('does not grow the leaderboard query count with the number of accounts', function () {
    $user = User::factory()->withPersonalTeam()->create();

    $addMembers = function (User $user, int $from, int $to): void {
        foreach (range($from, $to) as $index) {
            AccountHiscore::create([
                'account_id' => Account::factory()->for($user)->create(['username' => "Member{$index}"])->id,
                'entries' => ['skills' => ['attack' => ['rank' => $index, 'level' => 99, 'xp' => 13_034_431]], 'activities' => []],
                'fetched_at' => now(),
            ]);
        }
    };

    $addMembers($user, 1, 2);

    // See the account index budget above on why the first render is unmeasured.
    $this->actingAs($user)->get(route('hiscores.skills.index', 'attack'))->assertOk();

    $twoRows = sqlDuring(function () use ($user): void {
        $this->actingAs($user)->get(route('hiscores.skills.index', 'attack'))->assertOk();
    });

    $addMembers($user, 3, 12);

    $twelveRows = sqlDuring(function () use ($user): void {
        $this->actingAs($user)->get(route('hiscores.skills.index', 'attack'))->assertOk();
    });

    expect(count($twelveRows))->toBe(count($twoRows));
});
