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

it('renders stats up front and defers the chart and boards', function () {
    $player = WomPlayer::factory()->create(['ehp' => 50, 'ehb' => 5]);
    foreach ([[8, 1_000], [0, 4_000]] as [$daysAgo, $exp]) {
        WomDailySnapshot::query()->create([
            'account_id' => $player->account_id,
            'date' => now('UTC')->subDays($daysAgo)->toDateString(),
            'exp' => $exp, 'ehp' => 50.0, 'ehb' => 5.0,
        ]);
    }

    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('progress.index', ['period' => 'month', 'metric' => 'nope']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Progress/Index')
            ->where('filters', ['range' => 'year', 'period' => 'month', 'metric' => 'exp'])
            ->where('stats.active_members', 1)
            ->where('stats.week_exp_gained', 3_000)
            ->missing('series')
            ->missing('topGainers')
            ->missing('efficiency')
            ->loadDeferredProps(['series', 'gainers', 'efficiency'], fn (AssertableInertia $reload) => $reload
                ->has('series', 2)
                ->where('series.1.exp', 3_000)
                ->where('topGainers.0.gained', 3_000)
                ->where('efficiency.ehp.0.value', 50)
                ->has('efficiency.ehb', 1),
            ),
        );
});

it('masks names and drops account ids for guests when account names are hidden', function () {
    SettingHelper::setSetting('wom_progress_public', true, 'bool');
    SettingHelper::setSetting('public_anonymize_accounts', true, 'bool');

    $player = WomPlayer::factory()->for(Account::factory()->state(['username' => 'Zezima']))->create(['ehp' => 10, 'ehb' => 1]);
    foreach ([[8, 1_000], [0, 2_000]] as [$daysAgo, $exp]) {
        WomDailySnapshot::query()->create([
            'account_id' => $player->account_id,
            'date' => now('UTC')->subDays($daysAgo)->toDateString(),
            'exp' => $exp, 'ehp' => 10.0, 'ehb' => 1.0,
        ]);
    }

    $this->get(route('progress.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('anonymized', true)
            ->loadDeferredProps(['gainers', 'efficiency'], fn (AssertableInertia $reload) => $reload
                ->where('topGainers.0.username', 'Z•••••')
                ->missing('topGainers.0.account_id')
                ->where('efficiency.ehp.0.username', 'Z•••••')
                ->missing('efficiency.ehp.0.account_id')
                ->missing('efficiency.ehb.0.account_id'),
            ),
        );

    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('progress.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('anonymized', false)
            ->loadDeferredProps('gainers', fn (AssertableInertia $reload) => $reload
                ->where('topGainers.0.username', 'Zezima')
                ->where('topGainers.0.account_id', $player->account_id),
            ),
        );
});
