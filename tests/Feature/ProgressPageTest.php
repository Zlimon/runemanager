<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
});

it('is not found while Wise Old Man is off', function () {
    SettingHelper::setSetting('wom_enabled', false, 'bool');

    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('progress.index'))
        ->assertNotFound();
});

it('sends guests to login while the page is members-only', function () {
    $this->get(route('progress.index'))->assertRedirect(route('login'));
});

it('lets guests in once the page is public', function () {
    SettingHelper::setSetting('wom_progress_public', true, 'bool');

    $this->get(route('progress.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Progress/Index'));
});

/**
 * @param  array{0: array<string, int>, 1: array<string, int>}  $skills
 */
function womProgressPagePlayer(WomPlayer $player, array $exp, array $skills = [[], []]): void
{
    foreach ([8, 0] as $index => $daysAgo) {
        WomDailySnapshot::query()->insert([
            'account_id' => $player->account_id,
            'date' => now('UTC')->subDays($daysAgo)->toDateString(),
            'exp' => $exp[$index],
            'ehp' => 0.0,
            'ehb' => 0.0,
            'skills' => $skills[$index],
            'bosses' => [],
            'activities' => [],
        ]);
    }
}

it('renders stats and the metric catalogue up front and defers both boards', function () {
    $player = WomPlayer::factory()->create();
    womProgressPagePlayer($player, [1_000, 4_000], [['attack' => 0], ['attack' => 3_000]]);

    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('progress.index', ['period' => 'month', 'metric' => 'nope']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Progress/Index')
            ->where('filters', ['period' => 'month', 'metric' => 'overall'])
            ->where('stats.active_members', 1)
            ->where('stats.week_exp_gained', 3_000)
            ->where('stats.week_levels_gained', 15)
            ->where('stats.week_boss_kills', 0)
            ->where('metrics.skills.0', ['metric' => 'overall', 'label' => 'Overall', 'icon' => ['kind' => 'skill', 'slug' => 'overall']])
            ->has('metrics.bosses')
            ->has('metrics.activities')
            ->missing('series')
            ->missing('efficiency')
            ->missing('topGainers')
            ->missing('skillLeaders')
            ->loadDeferredProps(['gainers', 'leaders'], fn (AssertableInertia $reload) => $reload
                ->where('topGainers.0.gained', 3_000)
                ->has('skillLeaders', 1)
                ->where('skillLeaders.0.metric', 'attack')
                ->where('skillLeaders.0.gained', 3_000),
            ),
        );
});

it('ranks the top gainers on the metric asked for', function () {
    $player = WomPlayer::factory()->create();
    womProgressPagePlayer($player, [0, 50], [['cooking' => 100], ['cooking' => 150]]);

    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('progress.index', ['metric' => 'cooking']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.metric', 'cooking')
            ->loadDeferredProps('gainers', fn (AssertableInertia $reload) => $reload
                ->where('topGainers.0.account_id', $player->account_id)
                ->where('topGainers.0.gained', 50),
            ),
        );
});

it('masks names and drops account ids for guests when account names are hidden', function () {
    SettingHelper::setSetting('wom_progress_public', true, 'bool');
    SettingHelper::setSetting('public_anonymize_accounts', true, 'bool');

    $player = WomPlayer::factory()->for(Account::factory()->state(['username' => 'Zezima']))->create();
    womProgressPagePlayer($player, [1_000, 2_000], [['attack' => 0], ['attack' => 1_000]]);

    $this->get(route('progress.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('anonymized', true)
            ->loadDeferredProps(['gainers', 'leaders'], fn (AssertableInertia $reload) => $reload
                ->where('topGainers.0.username', 'Z•••••')
                ->missing('topGainers.0.account_id')
                ->where('skillLeaders.0.username', 'Z•••••')
                ->missing('skillLeaders.0.account_id'),
            ),
        );

    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('progress.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('anonymized', false)
            ->loadDeferredProps(['gainers', 'leaders'], fn (AssertableInertia $reload) => $reload
                ->where('topGainers.0.username', 'Zezima')
                ->where('topGainers.0.account_id', $player->account_id)
                ->where('skillLeaders.0.account_id', $player->account_id),
            ),
        );
});
