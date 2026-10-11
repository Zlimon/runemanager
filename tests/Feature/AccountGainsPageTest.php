<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Models\WomAchievement;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Models\WomRecord;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Http::preventStrayRequests();
    // The Vite dev server's SSR endpoint would count as a stray request.
    config(['inertia.ssr.enabled' => false]);
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->account = Account::factory()->for($this->user)->create(['username' => 'Meekss']);
});

it('sends no Wise Old Man summary on the Log while the integration is off', function () {
    WomPlayer::factory()->for($this->account)->create();

    $this->actingAs($this->user)
        ->get(route('accounts.show', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Show')
            ->missing('wom'),
        );

    $this->actingAs($this->user)
        ->get(route('accounts.gains', $this->account))
        ->assertNotFound();
});

it('sends the summary on the Log while the integration is on', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    WomPlayer::factory()->for($this->account)->create(['ehp' => 832.26]);

    $this->actingAs($this->user)
        ->get(route('accounts.show', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('wom.linked', true)
            ->where('wom.ehp', 832.26),
        );
});

it('renders the Gains tab with filters, deferred gains and series, records and achievements', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    WomPlayer::factory()->for($this->account)->create(['ehp' => 832.26]);

    WomDailySnapshot::query()->insert([
        [
            'account_id' => $this->account->id,
            'date' => now('UTC')->subDays(10)->toDateString(),
            'exp' => 1000, 'ehp' => 1.0, 'ehb' => 0.0,
            'skills' => ['slayer' => 400], 'bosses' => ['zulrah' => 10], 'activities' => [],
        ],
        [
            'account_id' => $this->account->id,
            'date' => now('UTC')->subDay()->toDateString(),
            'exp' => 1600, 'ehp' => 1.5, 'ehb' => 0.1,
            'skills' => ['slayer' => 1000], 'bosses' => ['zulrah' => 25], 'activities' => [],
        ],
    ]);

    WomRecord::factory()->for($this->account)->kills('zulrah', 15)->period('month')->create();
    WomAchievement::factory()->for($this->account)->kills('zulrah', 100)->create();

    $this->actingAs($this->user)
        ->get(route('accounts.gains', ['account' => $this->account, 'period' => 'month', 'metric' => 'slayer', 'range' => 'bogus']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Gains/Show')
            ->where('wom.linked', true)
            ->where('filters', ['period' => 'month', 'metric' => 'slayer', 'range' => 'year'])
            ->has('records', 1)
            ->where('records.0.metric', 'zulrah')
            ->where('records.0.month.value', 15)
            ->has('achievements', 1)
            ->where('achievements.0.metric', 'zulrah')
            ->missing('gains')
            ->missing('metricSeries')
            ->loadDeferredProps('gains', fn (AssertableInertia $reload) => $reload
                ->where('gains.period', 'month')
                ->where('gains.bosses.0.metric', 'zulrah')
                ->where('gains.bosses.0.gained', 15),
            )
            ->loadDeferredProps('series', fn (AssertableInertia $reload) => $reload
                ->where('metricSeries.metric', 'slayer')
                ->has('metricSeries.points', 2)
                ->where('metricSeries.points.1.v', 1000),
            ),
        );
});

it('falls back to the default filters for unknown values', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');

    $this->actingAs($this->user)
        ->get(route('accounts.gains', ['account' => $this->account, 'period' => 'decade', 'metric' => 'nope', 'range' => 'forever']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('wom', null)
            ->where('filters', ['period' => 'week', 'metric' => 'overall', 'range' => 'year']),
        );
});

it('keeps the owner\'s consent choices from other viewers and has no gains for an unlinked account', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    WomPlayer::factory()->notFound()->for($this->account)->create(['tracking_opt_in' => true]);
    WomDailySnapshot::query()->create([
        'account_id' => $this->account->id,
        'date' => now('UTC')->toDateString(),
        'exp' => 1000, 'ehp' => 1.0, 'ehb' => 0.0,
    ]);

    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('accounts.gains', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('wom.not_found', true)
            ->missing('wom.tracking_opt_in')
            ->missing('wom.show_on_progress')
            ->loadDeferredProps(['gains', 'series'], fn (AssertableInertia $reload) => $reload
                ->where('gains.skills', [])
                ->where('gains.bosses', [])
                ->where('metricSeries.points', []),
            ),
        );

    Http::assertNothingSent();
});
