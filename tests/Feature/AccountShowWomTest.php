<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    // The Vite dev server's SSR endpoint would count as a stray request.
    config(['inertia.ssr.enabled' => false]);
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->account = Account::factory()->for($this->user)->create(['username' => 'Meekss']);
});

it('sends no Wise Old Man props while the integration is off', function () {
    WomPlayer::factory()->for($this->account)->create();

    $this->actingAs($this->user)
        ->get(route('accounts.show', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Show')
            ->missing('wom')
            ->missing('womSeries')
            ->missing('womGains')
            ->missing('womFilters'),
        );
});

it('sends the summary and defers the history and gains', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    config(['services.wiseoldman.url' => 'https://api.wiseoldman.net/v2']);
    Http::fake(['api.wiseoldman.net/v2/players/meekss/gained*' => Http::response(womFixture('gained-meekss-week'))]);

    WomPlayer::factory()->for($this->account)->create(['ehp' => 832.26]);
    WomDailySnapshot::query()->create([
        'account_id' => $this->account->id,
        'date' => now('UTC')->subDays(2)->toDateString(),
        'exp' => 1000, 'ehp' => 1.0, 'ehb' => 0.0,
    ]);

    $this->actingAs($this->user)
        ->get(route('accounts.show', ['account' => $this->account, 'wrange' => 'bogus', 'wperiod' => 'month']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('wom.linked', true)
            ->where('wom.ehp', 832.26)
            ->where('wom.show_on_progress', true)
            ->where('womFilters', ['range' => 'year', 'period' => 'month'])
            ->missing('womSeries')
            ->missing('womGains')
            ->loadDeferredProps('wom', fn (AssertableInertia $reload) => $reload
                ->has('womSeries', 1)
                ->where('womSeries.0.exp', 1000),
            )
            ->loadDeferredProps('womGains', fn (AssertableInertia $reload) => $reload
                ->has('womGains.headline')
                ->where('womGains.skills.0.metric', 'slayer'),
            ),
        );
});

it('sends a null summary for an account never looked up', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');

    $this->actingAs($this->user)
        ->get(route('accounts.show', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->where('wom', null));
});

it('keeps the owner\'s consent choices from other viewers and skips unlinked history', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    WomPlayer::factory()->notFound()->for($this->account)->create(['tracking_opt_in' => true]);
    WomDailySnapshot::query()->create([
        'account_id' => $this->account->id,
        'date' => now('UTC')->toDateString(),
        'exp' => 1000, 'ehp' => 1.0, 'ehb' => 0.0,
    ]);

    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('accounts.show', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('wom.not_found', true)
            ->missing('wom.tracking_opt_in')
            ->missing('wom.show_on_progress')
            ->loadDeferredProps(['wom', 'womGains'], fn (AssertableInertia $reload) => $reload
                ->where('womSeries', [])
                ->where('womGains.skills', [])
                ->where('womGains.headline.exp', 0),
            ),
        );

    Http::assertNothingSent();
});
