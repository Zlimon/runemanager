<?php

use App\Helpers\SettingHelper;
use App\Jobs\SyncWomPlayerJob;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\User;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\WomCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['inertia.ssr.enabled' => false]);
    Http::preventStrayRequests();
    Storage::fake('local');

    $this->owner = User::factory()->withPersonalTeam()->create();
    $this->account = Account::factory()->for($this->owner)->create(['username' => 'Mine']);

    $this->other = User::factory()->withPersonalTeam()->create();
    $this->theirs = Account::factory()->for($this->other)->create(['username' => 'Theirs']);
});

it('lists the owner\'s accounts with every switch and the counts they cover', function () {
    FeedEvent::factory()->for($this->account)->questComplete()->positioned()->create();
    FeedEvent::factory()->for($this->account)->death()->create(['screenshot_path' => 'feed-screenshots/a.png']);
    FeedEvent::factory()->for($this->account)->death()->create(['screenshot_path' => 'feed-screenshots/b.png', 'screenshot_hidden' => true]);
    FeedEvent::factory()->for($this->account)->death()->create();
    $this->account->forceFill(['show_events_on_map' => true, 'hide_new_screenshots' => true])->save();

    $this->actingAs($this->owner)
        ->get(route('privacy.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Privacy/Index')
            ->where('wom_enabled', false)
            ->has('accounts', 1)
            ->where('accounts.0', [
                'id' => $this->account->id,
                'username' => 'Mine',
                'account_type' => $this->account->account_type->value,
                'show_events_on_map' => true,
                'hide_new_screenshots' => true,
                'positioned_event_count' => 1,
                'screenshot_count' => 2,
                'hidden_screenshot_count' => 1,
                'wom' => null,
            ]),
        );
});

it('shows the Wise Old Man status while the integration is on', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    $notFound = Account::factory()->for($this->owner)->create(['username' => 'Zed']);
    WomPlayer::factory()->notFound()->for($notFound)->create();

    $this->actingAs($this->owner)
        ->get(route('privacy.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('wom_enabled', true)
            ->where('accounts.0.wom.status', 'unchecked')
            ->where('accounts.1.wom.status', 'not_found'));
});

it('requires a login', function () {
    $this->get(route('privacy.index'))->assertRedirect(route('login'));
});

it('saves each account switch on its own', function (string $field) {
    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), [$field => true])
        ->assertRedirect();

    expect($this->account->fresh()->{$field})->toBeTrue();

    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), [$field => false]);

    expect($this->account->fresh()->{$field})->toBeFalse();
})->with(['show_events_on_map', 'hide_new_screenshots']);

it('saves the Wise Old Man choices through the WOM preferences, bumping the cache', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    Queue::fake();
    WomPlayer::factory()->for($this->account)->create();
    $before = WomCache::version();

    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), ['show_on_progress' => false])
        ->assertRedirect();

    expect($this->account->womPlayer()->first()->show_on_progress)->toBeFalse()
        ->and(WomCache::version())->toBeGreaterThan($before);

    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), ['tracking_opt_in' => true])
        ->assertSessionHas('flash.banner', 'Queued — Wise Old Man will update shortly.');

    expect($this->account->womPlayer()->first()->tracking_opt_in)->toBeTrue();
    Queue::assertPushed(SyncWomPlayerJob::class);

    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), ['tracking_opt_in' => false]);

    expect($this->account->womPlayer()->first()->tracking_opt_in)->toBeFalse();
});

it('ignores the Wise Old Man choices while the integration is off', function () {
    Queue::fake();
    $before = WomCache::version();

    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), ['show_on_progress' => false, 'tracking_opt_in' => true, 'hide_new_screenshots' => true])
        ->assertRedirect()
        ->assertSessionMissing('flash.banner');

    expect(WomPlayer::count())->toBe(0)
        ->and(WomCache::version())->toBe($before)
        ->and($this->account->fresh()->hide_new_screenshots)->toBeTrue();
    Queue::assertNothingPushed();
});

it('forbids changing someone else\'s account, before validation', function () {
    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->theirs), ['show_events_on_map' => 'maybe'])
        ->assertForbidden();

    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->theirs), ['show_events_on_map' => true])
        ->assertForbidden();

    expect($this->theirs->fresh()->show_events_on_map)->toBeFalse();
});

it('validates the switches', function () {
    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), ['show_events_on_map' => 'maybe', 'hide_new_screenshots' => 'x'])
        ->assertSessionHasErrors(['show_events_on_map', 'hide_new_screenshots']);
});

it('throttles the switches in their own bucket', function () {
    // Wise Old Man saves do not use up the privacy allowance…
    foreach (range(1, 6) as $attempt) {
        $this->actingAs($this->owner)->put(route('wise-old-man.update', $this->account), ['show_on_progress' => false]);
    }

    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), ['show_events_on_map' => true])
        ->assertRedirect();

    // …and many accounts can be toggled in a minute, up to 30 saves.
    foreach (range(2, 30) as $attempt) {
        $this->actingAs($this->owner)->put(route('privacy.update', $this->account), ['show_events_on_map' => true]);
    }

    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), ['show_events_on_map' => true])
        ->assertTooManyRequests();

    // The Wise Old Man routes keep their own limit.
    $this->actingAs($this->owner)
        ->put(route('wise-old-man.update', $this->account), ['show_on_progress' => false])
        ->assertTooManyRequests();
});

it('queues a manual Wise Old Man sync once a day per account', function () {
    $this->freezeSecond();
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    Queue::fake();

    $this->actingAs($this->owner)
        ->post(route('privacy.wom-sync', $this->account))
        ->assertRedirect()
        ->assertSessionHas('flash.banner', 'Queued — Wise Old Man will update shortly.');

    Queue::assertPushed(SyncWomPlayerJob::class, fn (SyncWomPlayerJob $job) => $job->accountId === $this->account->id && $job->requestUpdate);

    $this->actingAs($this->owner)
        ->get(route('privacy.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('accounts.0.wom.sync_available_at', now()->addDay()->toIso8601String()));

    $this->travel(23)->hours();

    $this->actingAs($this->owner)
        ->post(route('privacy.wom-sync', $this->account))
        ->assertSessionHas('flash.bannerStyle', 'danger');

    Queue::assertPushed(SyncWomPlayerJob::class, 1);

    $this->travel(1)->hours();

    $this->actingAs($this->owner)
        ->post(route('privacy.wom-sync', $this->account))
        ->assertSessionHas('flash.bannerStyle', 'success');

    Queue::assertPushed(SyncWomPlayerJob::class, 2);
});

it('only lets the owner sync an account, and only while the integration is on', function () {
    Queue::fake();

    $this->actingAs($this->owner)
        ->post(route('privacy.wom-sync', $this->account))
        ->assertNotFound();

    SettingHelper::setSetting('wom_enabled', true, 'bool');

    $this->actingAs($this->owner)
        ->post(route('privacy.wom-sync', $this->theirs))
        ->assertForbidden();

    Queue::assertNothingPushed();
});
