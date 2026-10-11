<?php

use App\Models\Account;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\FeedEvent;
use App\Models\NewsPost;
use App\Models\PluginRequest;
use App\Models\User;
use App\Services\Instance\AdminOverview;
use App\Support\Roles;
use Database\Factories\ResourcePackFactory;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;

/**
 * Log one plugin request directly (mirrors PluginTrafficTest's trafficLog(),
 * kept local so this file doesn't depend on another test file having been
 * loaded first). `flagged: true` gives it a flag so it counts toward
 * PluginTraffic::flaggedCount().
 */
function overviewTrafficLog(bool $flagged = false, int $minutesAgo = 1): void
{
    PluginRequest::query()->insert([
        'user_id' => null,
        'account_id' => null,
        'account_hash' => null,
        'username' => null,
        'account_type' => null,
        'login_email' => null,
        'route' => 'api.plugin.heartbeat',
        'method' => 'PUT',
        'path' => '/api/plugin/heartbeat',
        'status' => $flagged ? 429 : 200,
        'duration_ms' => 10.0,
        'request_bytes' => 2,
        'ip' => '10.0.0.1',
        'user_agent' => 'RuneLite/1.11',
        'flags' => $flagged ? ['throttled'] : [],
        'created_at' => new UTCDateTime(now()->subMinutes($minutesAgo)),
    ]);
}

it('counts every overview metric', function () {
    Roles::sync();

    // Every related factory below is given an explicit owner user_id /
    // account_id — their defaults spin up a brand new related model
    // (User::factory() / Account::factory()), which would silently inflate
    // the users/accounts counts this test asserts on.
    $owner = tap(User::factory()->create())->assignRole(Roles::OWNER);
    User::factory()->create(['suspended_at' => now()]);
    User::factory()->count(2)->create();

    // Unclaimed (no user_id): AccountFactory's own default is User::factory(),
    // which would otherwise add 3 more rows to the users count above.
    $account = Account::factory()->create(['user_id' => null]);
    Account::factory()->count(2)->create(['user_id' => null]);

    FeedEvent::factory()->withScreenshot()->count(2)->create(['account_id' => $account->id]);
    FeedEvent::factory()->withScreenshot()->create(['account_id' => $account->id, 'screenshot_hidden' => true]);
    FeedEvent::factory()->create(['account_id' => $account->id]); // no screenshot — not counted

    NewsPost::factory()->draft()->count(2)->create(['user_id' => $owner->id]);
    NewsPost::factory()->scheduled()->create(['user_id' => $owner->id]);
    NewsPost::factory()->count(3)->create(['user_id' => $owner->id]); // published by default

    CalendarEvent::factory()->count(2)->create(['user_id' => $owner->id]); // upcoming by default
    CalendarEvent::factory()->past()->create(['user_id' => $owner->id]);

    Announcement::factory()->count(2)->create(['user_id' => $owner->id]); // active by default
    Announcement::factory()->expired()->create(['user_id' => $owner->id]);

    ResourcePackFactory::new()->vanilla()->create();
    ResourcePackFactory::new()->create(['version' => 'pending']);

    overviewTrafficLog();
    overviewTrafficLog();
    overviewTrafficLog(flagged: true);
    overviewTrafficLog(minutesAgo: 60 * 25); // outside the 24h window — not counted

    $stats = app(AdminOverview::class)->stats();

    expect($stats)->toBe([
        'users' => 4,
        'suspended_users' => 1,
        'accounts' => 3,
        'screenshots' => 3,
        'hidden_screenshots' => 1,
        'news_drafts' => 2,
        'news_scheduled' => 1,
        'news_published' => 3,
        'upcoming_events' => 2,
        'active_announcements' => 2,
        'installed_packs' => 1,
        'requests_24h' => 3,
        'flagged_24h' => 1,
    ]);
});

it('issues a constant number of queries regardless of data volume', function () {
    Roles::sync();
    $owner = tap(User::factory()->create())->assignRole(Roles::OWNER);

    DB::enableQueryLog();
    app(AdminOverview::class)->stats();
    $queriesForFewRows = count(DB::getQueryLog());
    DB::flushQueryLog();

    // DB::enableQueryLog() is still on from above — flush again right before
    // the second stats() call, so this batch's own insert queries aren't
    // counted alongside it.
    User::factory()->count(20)->create();
    Account::factory()->count(20)->create(['user_id' => null]);
    NewsPost::factory()->count(20)->create(['user_id' => $owner->id]);
    CalendarEvent::factory()->count(20)->create(['user_id' => $owner->id]);
    Announcement::factory()->count(20)->create(['user_id' => $owner->id]);
    ResourcePackFactory::new()->count(20)->create();
    DB::flushQueryLog();

    app(AdminOverview::class)->stats();
    $queriesForManyRows = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Every metric is a single count() query, so the total is independent of
    // how many rows exist — no per-row (N+1) queries anywhere in stats().
    expect($queriesForManyRows)->toBe($queriesForFewRows);
});

it('surfaces the top suspicious users, capped at the given limit', function () {
    // PluginTraffic::suspiciousUsers() lists an anonymous IP once it has at
    // least MAX_ANONYMOUS_FAILURES (10) unauthenticated requests in the window.
    foreach (range(1, 10) as $i) {
        PluginRequest::query()->insert([
            'user_id' => null,
            'account_id' => null,
            'account_hash' => null,
            'username' => null,
            'account_type' => null,
            'login_email' => null,
            'route' => 'api.login',
            'method' => 'POST',
            'path' => '/api/login',
            'status' => 401,
            'duration_ms' => 5.0,
            'request_bytes' => 2,
            'ip' => '10.0.0.9',
            'user_agent' => 'RuneLite/1.11',
            'flags' => ['unauthenticated'],
            'created_at' => new UTCDateTime(now()->subMinutes($i)),
        ]);
    }

    $suspicious = app(AdminOverview::class)->suspiciousUsers(5);

    expect($suspicious)->toHaveCount(1);
    expect($suspicious[0])->toMatchArray(['user_id' => null, 'ip' => '10.0.0.9']);
    expect($suspicious[0]['reasons'])->toContain('10 unauthenticated requests');

    expect(app(AdminOverview::class)->suspiciousUsers(0))->toBe([]);
});

it('caches the traffic figures so a second read does not re-aggregate', function () {
    overviewTrafficLog();

    $overview = app(AdminOverview::class);
    expect($overview->stats()['requests_24h'])->toBe(1);

    // Within the cache window, a request logged after the first read is not
    // reflected — proves the count came from cache, not a fresh aggregation.
    overviewTrafficLog();
    expect($overview->stats()['requests_24h'])->toBe(1);
});
