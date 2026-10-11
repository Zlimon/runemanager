<?php

use App\Helpers\SettingHelper;
use App\Jobs\SyncWomPlayerJob;
use App\Models\Account;
use App\Models\User;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\WomCache;
use App\Services\WiseOldMan\WomLeaderboard;
use App\Services\WiseOldMan\WomProgress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Http::preventStrayRequests();
    // The Vite dev server's SSR endpoint would count as a stray request.
    config(['inertia.ssr.enabled' => false]);
    RateLimiter::clear('wiseoldman:requests');
    config(['services.wiseoldman.url' => 'https://api.wiseoldman.net/v2', 'services.wiseoldman.api_key' => null]);
    SettingHelper::setSetting('wom_enabled', true, 'bool');

    $this->owner = User::factory()->withPersonalTeam()->create();
    $this->account = Account::factory()->for($this->owner)->create(['username' => 'Bartheez']);
});

it('sends the old settings page to Privacy', function () {
    $this->actingAs($this->owner)
        ->get(route('wise-old-man.index'))
        ->assertRedirect(route('privacy.index'));
});

it('lists the user\'s own accounts with their link status on the Privacy page', function () {
    $player = WomPlayer::factory()->for($this->account)->create(['tracking_opt_in' => true]);
    $unchecked = Account::factory()->for($this->owner)->create(['username' => 'Zed']);
    Account::factory()->create(['username' => 'SomeoneElse']);

    $this->actingAs($this->owner)
        ->get(route('privacy.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Privacy/Index')
            ->where('wom_enabled', true)
            ->has('accounts', 2)
            ->where('accounts.0.username', 'Bartheez')
            ->where('accounts.0.wom', ['status' => 'linked', 'show_on_progress' => true, 'tracking_opt_in' => true, 'synced_at' => $player->synced_at->toIso8601String(), 'sync_available_at' => null])
            ->where('accounts.1.id', $unchecked->id)
            ->where('accounts.1.wom', ['status' => 'unchecked', 'show_on_progress' => true, 'tracking_opt_in' => false, 'synced_at' => null, 'sync_available_at' => null]),
        );
});

it('is not found while Wise Old Man is off, apart from the redirect', function () {
    SettingHelper::setSetting('wom_enabled', false, 'bool');

    $this->actingAs($this->owner)->get(route('wise-old-man.index'))->assertRedirect(route('privacy.index'));
    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['show_on_progress' => false])
        ->assertNotFound();
    $this->actingAs($this->owner)->post(route('wise-old-man.track', $this->account))->assertNotFound();
});

it('requires a login', function () {
    $this->get(route('wise-old-man.index'))->assertRedirect(route('login'));
});

it('forbids changing someone else\'s account', function () {
    $stranger = User::factory()->withPersonalTeam()->create();

    $this->actingAs($stranger)
        ->put(route('wise-old-man.update', $this->account), ['show_on_progress' => false])
        ->assertForbidden();
    $this->actingAs($stranger)
        ->post(route('wise-old-man.track', $this->account))
        ->assertForbidden();

    expect(WomPlayer::count())->toBe(0);
    Http::assertNothingSent();
});

it('updates the progress toggle on an existing row', function () {
    $player = WomPlayer::factory()->for($this->account)->create();

    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['show_on_progress' => false])
        ->assertRedirect();

    expect($player->fresh()->show_on_progress)->toBeFalse();
});

it('creates the row when the toggle is saved before any lookup', function () {
    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['show_on_progress' => false])
        ->assertRedirect();

    expect($this->account->womPlayer()->first())
        ->show_on_progress->toBeFalse()
        ->wom_player_id->toBeNull();
});

it('validates the toggle', function () {
    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['show_on_progress' => 'maybe'])
        ->assertSessionHasErrors('show_on_progress');
});

it('queues a WOM update when the owner opts in through the track endpoint', function () {
    Queue::fake();
    WomPlayer::factory()->notFound()->for($this->account)->create();

    $this->actingAs($this->owner)
        ->post(route('wise-old-man.track', $this->account))
        ->assertRedirect();

    expect($this->account->womPlayer()->first()->tracking_opt_in)->toBeTrue();
    Queue::assertPushed(SyncWomPlayerJob::class, fn (SyncWomPlayerJob $job) => $job->accountId === $this->account->id && $job->requestUpdate);
    Http::assertNothingSent();
});

it('opts in and revokes tracking through the settings toggle', function () {
    Queue::fake();
    $player = WomPlayer::factory()->for($this->account)->create();

    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['tracking_opt_in' => true])
        ->assertRedirect();

    expect($player->fresh()->tracking_opt_in)->toBeTrue()
        ->and($player->fresh()->show_on_progress)->toBeTrue();
    Queue::assertPushed(SyncWomPlayerJob::class, 1);

    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['tracking_opt_in' => false])
        ->assertRedirect();

    expect($player->fresh()->tracking_opt_in)->toBeFalse();
    Queue::assertPushed(SyncWomPlayerJob::class, 1);
});

it('requires at least one choice', function () {
    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), [])
        ->assertSessionHasErrors('show_on_progress');
});

it('throttles the write endpoints', function () {
    Queue::fake();

    foreach (range(1, 6) as $attempt) {
        $this->actingAs($this->owner)
            ->put(route('wise-old-man.update', $this->account), ['show_on_progress' => true])
            ->assertRedirect();
    }

    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['show_on_progress' => true])
        ->assertTooManyRequests();
});

it('drops a hidden account from the cached boards and stats at once', function () {
    $player = WomPlayer::factory()->for($this->account)->create(['ehp' => 40]);
    foreach ([[8, 1_000], [0, 5_000]] as [$daysAgo, $exp]) {
        WomDailySnapshot::query()->create([
            'account_id' => $this->account->id,
            'date' => now('UTC')->subDays($daysAgo)->toDateString(),
            'exp' => $exp, 'ehp' => 40.0, 'ehb' => 0.0,
        ]);
    }

    // Warm the dashboard-sized and /progress-sized boards and the stats.
    expect(app(WomLeaderboard::class)->topGainers('week', 'exp', 5))->toHaveCount(1)
        ->and(app(WomLeaderboard::class)->topGainers('week', 'exp'))->toHaveCount(1)
        ->and(app(WomProgress::class)->stats()['active_members'])->toBe(1);

    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['show_on_progress' => false])
        ->assertRedirect();

    expect(app(WomLeaderboard::class)->topGainers('week', 'exp', 5))->toBe([])
        ->and(app(WomLeaderboard::class)->topGainers('week', 'exp'))->toBe([])
        ->and(app(WomProgress::class)->stats())->toMatchArray(['active_members' => 0, 'week_exp_gained' => 0]);
});

it('retires the cached boards when the owner withdraws consent', function () {
    WomPlayer::factory()->for($this->account)->create(['tracking_opt_in' => true]);
    $before = WomCache::version();

    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['tracking_opt_in' => false])
        ->assertRedirect();

    expect(WomCache::version())->toBeGreaterThan($before);
});
