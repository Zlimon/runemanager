<?php

use App\Console\Commands\PrunePluginRequests;
use App\Helpers\SettingHelper;
use App\Http\Middleware\LimitPluginRequestsByIp;
use App\Http\Middleware\LogPluginRequest;
use App\Models\Account;
use App\Models\PluginRequest;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\Instance\ResetInstanceData;
use App\Services\Plugin\PluginTraffic;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;
use MongoDB\BSON\UTCDateTime;

uses(RefreshDatabase::class);

function trafficUser(string $email = 'traffic@test.local'): User
{
    return User::query()->forceCreate([
        'name' => 'Traffic User',
        'email' => $email,
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function trafficHeaders(string $hash = 'hash-traffic', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
        'X-Account-Type' => 'normal',
    ];
}

/**
 * Seed one logged request directly, for the aggregation tests.
 *
 * @param  array<string, mixed>  $attributes
 */
function trafficLog(array $attributes = []): void
{
    $minutesAgo = $attributes['minutes_ago'] ?? 1;
    unset($attributes['minutes_ago']);

    PluginRequest::query()->insert(array_merge([
        'user_id' => null,
        'account_id' => null,
        'account_hash' => null,
        'username' => null,
        'account_type' => null,
        'login_email' => null,
        'login_email_hash' => null,
        'route' => 'api.plugin.heartbeat',
        'method' => 'PUT',
        'path' => '/api/plugin/heartbeat',
        'status' => 200,
        'duration_ms' => 10.0,
        'request_bytes' => 2,
        'ip' => '10.0.0.1',
        'user_agent' => 'RuneLite/1.11',
        'flags' => [],
        'created_at' => new UTCDateTime(now()->subMinutes($minutesAgo)),
    ], $attributes));
}

it('logs the metadata of a successful push without its body', function () {
    $user = trafficUser();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/inventory', [
        'inventory' => [[995, 10000], [4151, 1], [385, 20]],
    ], trafficHeaders())->assertSuccessful();

    $log = PluginRequest::query()->sole();

    expect($log->user_id)->toBe($user->id)
        ->and($log->account_id)->toBe(Account::query()->sole()->id)
        ->and($log->account_hash)->toBe('hash-traffic')
        ->and($log->username)->toBe('Zlimon')
        ->and($log->account_type)->toBe('normal')
        ->and($log->route)->toBe('api.plugin.inventory')
        ->and($log->method)->toBe('PUT')
        ->and($log->path)->toBe('/api/plugin/inventory')
        ->and($log->status)->toBe(200)
        ->and($log->request_bytes)->toBeGreaterThan(30)
        ->and($log->duration_ms)->toBeGreaterThanOrEqual(0)
        ->and($log->flags)->toBe([])
        ->and(array_keys($log->getAttributes()))->not->toContain('inventory', 'body', 'payload', 'content');
});

it('logs login attempts with a known email but never the password', function () {
    $user = trafficUser('login@test.local');

    $this->postJson('/api/login', ['email' => 'login@test.local', 'password' => 'wrong'])->assertUnauthorized();
    $this->postJson('/api/login', ['email' => 'login@test.local', 'password' => 'pw'])->assertOk();

    [$failed, $succeeded] = PluginRequest::query()->orderBy('_id')->get()->all();

    expect($failed->login_email)->toBe('login@test.local')
        ->and($failed->login_email_hash)->toBe(hash('sha256', 'login@test.local'))
        ->and($failed->user_id)->toBe($user->id)
        ->and($failed->flags)->toBe([LogPluginRequest::FLAG_UNAUTHENTICATED])
        ->and($succeeded->user_id)->toBe($user->id)
        ->and($succeeded->flags)->toBe([])
        ->and(json_encode(PluginRequest::query()->get()->toArray()))->not->toContain('wrong')->not->toContain('"pw"');
});

it('keeps only a hash of a login email no user has', function () {
    $this->postJson('/api/login', ['email' => 'hunter2-oops', 'password' => 'x'])->assertUnprocessable();

    $log = PluginRequest::query()->sole();

    expect($log->login_email)->toBeNull()
        ->and($log->login_email_hash)->toBe(hash('sha256', 'hunter2-oops'))
        ->and(json_encode($log->toArray()))->not->toContain('hunter2');
});

it('still logs a request whose headers are not valid UTF-8', function () {
    $this->putJson('/api/plugin/heartbeat', [], trafficHeaders("hash-\xff") + ['User-Agent' => "RuneLite\xff"])
        ->assertUnauthorized();

    $log = PluginRequest::query()->sole();

    expect(mb_check_encoding($log->user_agent, 'UTF-8'))->toBeTrue()
        ->and($log->user_agent)->toStartWith('RuneLite')
        ->and(mb_check_encoding($log->account_hash, 'UTF-8'))->toBeTrue();
});

it('takes the body size from Content-Length without reading the body', function () {
    Queue::fake();
    Sanctum::actingAs(trafficUser());

    $this->call('PUT', '/api/plugin/hiscores', server: [
        'CONTENT_LENGTH' => '12345',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_ACCOUNT_HASH' => 'hash-traffic',
        'HTTP_X_ACCOUNT_USERNAME' => 'Zlimon',
    ])->assertSuccessful();

    expect(PluginRequest::query()->sole()->request_bytes)->toBe(12345);
});

it('refuses an IP past its ceiling before authenticating, and logs it', function () {
    foreach (range(1, LimitPluginRequestsByIp::PER_MINUTE) as $hit) {
        RateLimiter::hit(LimitPluginRequestsByIp::key('127.0.0.1'), 60);
    }

    $this->putJson('/api/plugin/heartbeat', [], trafficHeaders())->assertTooManyRequests();

    expect(PluginRequest::query()->sole()->flags)->toBe([LogPluginRequest::FLAG_THROTTLED]);
});

it('bounds how many rejected requests one IP gets logged', function () {
    foreach (range(1, LogPluginRequest::REJECTIONS_LOGGED_PER_MINUTE + 5) as $attempt) {
        $this->putJson('/api/plugin/heartbeat', [], trafficHeaders())->assertUnauthorized();
    }

    expect(PluginRequest::query()->count())->toBe(LogPluginRequest::REJECTIONS_LOGGED_PER_MINUTE);
});

it('flags an unauthenticated plugin push', function () {
    $this->putJson('/api/plugin/heartbeat', [], trafficHeaders())->assertUnauthorized();

    expect(PluginRequest::query()->sole()->flags)->toBe([LogPluginRequest::FLAG_UNAUTHENTICATED]);
});

it('flags missing account headers apart from a failed validation', function () {
    Sanctum::actingAs(trafficUser());

    $this->putJson('/api/plugin/heartbeat', [], ['Accept' => 'application/json'])->assertUnprocessable();
    $this->putJson('/api/plugin/vitals', ['hitpoints' => 'lots'], trafficHeaders())->assertUnprocessable();

    expect(PluginRequest::query()->orderBy('_id')->pluck('flags')->all())->toBe([
        [LogPluginRequest::FLAG_MISSING_HEADERS],
        [LogPluginRequest::FLAG_VALIDATION_FAILED],
    ]);
});

it('flags a push for an account hash linked to another user', function () {
    Sanctum::actingAs(trafficUser('owner@test.local'));
    $this->putJson('/api/plugin/heartbeat', [], trafficHeaders('hash-owned'))->assertSuccessful();

    Sanctum::actingAs(trafficUser('thief@test.local'));
    $this->putJson('/api/plugin/heartbeat', [], trafficHeaders('hash-owned', 'Someone'))->assertForbidden();

    expect(PluginRequest::query()->orderBy('_id', 'desc')->first()->flags)
        ->toBe([LogPluginRequest::FLAG_ACCOUNT_CONFLICT]);
});

it('flags a plain 403 as forbidden and a suspended user as suspended', function () {
    SettingHelper::setSetting('instance_mode', 'clan');
    Sanctum::actingAs(trafficUser());
    $this->putJson('/api/plugin/heartbeat', [], trafficHeaders())->assertForbidden();

    $suspended = trafficUser('suspended@test.local');
    $suspended->forceFill(['suspended_at' => now()])->save();
    Sanctum::actingAs($suspended);
    $this->putJson('/api/plugin/heartbeat', [], trafficHeaders('hash-2'))->assertForbidden();

    expect(PluginRequest::query()->orderBy('_id')->pluck('flags')->all())->toBe([
        [LogPluginRequest::FLAG_FORBIDDEN],
        [LogPluginRequest::FLAG_SUSPENDED],
    ]);
});

it('flags a body larger than the route ever needs', function () {
    Sanctum::actingAs(trafficUser());

    $this->putJson('/api/plugin/heartbeat', ['junk' => str_repeat('a', LogPluginRequest::DEFAULT_MAX_BYTES)], trafficHeaders())
        ->assertSuccessful();

    expect(PluginRequest::query()->sole()->flags)->toBe([LogPluginRequest::FLAG_OVERSIZED]);
});

it('throttles a route past its limit per user and logs the 429', function () {
    Queue::fake();
    Sanctum::actingAs(trafficUser());
    $limit = AppServiceProvider::PLUGIN_RATE_LIMITS['plugin-heavy'];

    for ($i = 0; $i < $limit; $i++) {
        $this->putJson('/api/plugin/hiscores', [], trafficHeaders())->assertSuccessful();
    }

    $this->putJson('/api/plugin/hiscores', [], trafficHeaders())->assertTooManyRequests();

    // Another route keeps its own budget.
    $this->putJson('/api/plugin/heartbeat', [], trafficHeaders())->assertSuccessful();

    expect(PluginRequest::query()->where('flags', LogPluginRequest::FLAG_THROTTLED)->count())->toBe(1)
        ->and(PluginRequest::query()->where('status', 429)->sole()->route)->toBe('api.plugin.hiscores');
});

it('buys no fresh budget with a new account hash, and provisions nothing when throttled', function () {
    Queue::fake();
    Sanctum::actingAs(trafficUser());

    foreach (range(1, AppServiceProvider::PLUGIN_RATE_LIMITS['plugin-heavy']) as $attempt) {
        $this->putJson('/api/plugin/hiscores', [], trafficHeaders())->assertSuccessful();
    }

    $this->putJson('/api/plugin/hiscores', [], trafficHeaders('hash-rotated', 'Rotated'))->assertTooManyRequests();

    expect(Account::query()->where('account_hash', 'hash-rotated')->exists())->toBeFalse()
        ->and(Account::query()->count())->toBe(1);
});

it('caps a user\'s whole limiter group as well as each route', function () {
    $user = trafficUser();
    $request = Request::create('/api/plugin/avatar', 'POST', server: ['HTTP_X_ACCOUNT_HASH' => 'hash-a']);
    $request->setUserResolver(fn () => $user);
    $request->setRouteResolver(fn () => app('router')->getRoutes()->getByName('api.plugin.avatar'));

    $limits = RateLimiter::limiter('plugin-heavy')($request);
    $perRoute = AppServiceProvider::PLUGIN_RATE_LIMITS['plugin-heavy'];

    expect(array_map(fn (Limit $limit): array => [$limit->key, $limit->maxAttempts], $limits))->toBe([
        ["user:{$user->id}|route:api.plugin.avatar", $perRoute],
        ["user:{$user->id}|group:plugin-heavy", $perRoute * AppServiceProvider::PLUGIN_GROUP_FACTOR],
    ]);
});

it('prunes request metadata past the retention window', function () {
    trafficLog(['minutes_ago' => 60 * 24 * 20]);
    trafficLog(['minutes_ago' => 60 * 24 * 10]);

    $this->artisan('plugin-traffic:prune')->assertSuccessful();

    expect(PrunePluginRequests::RETENTION_DAYS)->toBe(14)
        ->and(PluginRequest::query()->count())->toBe(1);
});

it('logs a live ping only when it fails or raises a flag', function () {
    Sanctum::actingAs(trafficUser());

    $this->putJson('/api/plugin/heartbeat', [], trafficHeaders())->assertSuccessful();
    $this->putJson('/api/plugin/vitals', ['hitpoints' => 62, 'hitpoints_max' => 99, 'prayer' => 40,
        'prayer_max' => 70, 'run_energy' => 85, 'special_attack' => 50], trafficHeaders())->assertSuccessful();
    expect(PluginRequest::query()->count())->toBe(0);

    $this->putJson('/api/plugin/vitals', ['hitpoints' => 'lots'], trafficHeaders())->assertUnprocessable();
    expect(PluginRequest::query()->sole()->flags)->toBe([LogPluginRequest::FLAG_VALIDATION_FAILED]);
});

it('attributes a refused login to the user who owns the address', function () {
    $user = trafficUser('suspended-login@test.local');
    $user->forceFill(['suspended_at' => now()])->save();

    $this->postJson('/api/login', ['email' => 'suspended-login@test.local', 'password' => 'pw'])->assertForbidden();

    $log = PluginRequest::query()->sole();
    expect($log->user_id)->toBe($user->id)
        ->and($log->flags)->toBe([LogPluginRequest::FLAG_SUSPENDED]);
});

it('filters the feed by a route that matched no named route, by its path', function () {
    trafficLog(['route' => null, 'path' => '/api/nope', 'status' => 404, 'flags' => ['forbidden']]);
    trafficLog(['status' => 422, 'flags' => ['validation_failed']]);

    $traffic = app(PluginTraffic::class);
    $pathRow = collect($traffic->routes('1h'))->firstWhere('route', '/api/nope');

    expect($pathRow['requests'])->toBe(1)
        ->and($traffic->requests('1h', ['route' => '/api/nope'])->total())->toBe(1)
        ->and($traffic->requests('1h', ['route' => 'api.plugin.heartbeat'])->total())->toBe(1);
});

it('is wiped with the rest of the account data on an instance reset', function () {
    trafficLog();

    app(ResetInstanceData::class)->run();

    expect(PluginRequest::query()->count())->toBe(0);
});

it('aggregates totals, routes, users and flags over a window', function () {
    $busy = trafficUser('busy@test.local');
    $quiet = trafficUser('quiet@test.local');

    foreach (range(1, 18) as $i) {
        trafficLog(['user_id' => $busy->id, 'account_hash' => 'hash-a', 'duration_ms' => (float) $i, 'request_bytes' => 100]);
    }
    trafficLog(['user_id' => $busy->id, 'account_hash' => 'hash-b', 'route' => 'api.plugin.bank', 'status' => 429, 'flags' => ['throttled']]);
    trafficLog(['user_id' => $busy->id, 'account_hash' => 'hash-a', 'route' => 'api.plugin.bank', 'status' => 422, 'flags' => ['validation_failed']]);
    trafficLog(['user_id' => $quiet->id, 'account_hash' => 'hash-q']);
    trafficLog(['user_id' => $quiet->id, 'minutes_ago' => 60 * 3]);

    $traffic = app(PluginTraffic::class);

    expect($traffic->requestCount('1h'))->toBe(21)
        ->and($traffic->requestCount('24h'))->toBe(22)
        ->and($traffic->flaggedCount('1h'))->toBe(2);

    expect($traffic->summary('1h'))->toMatchArray([
        'requests' => 21, 'users' => 2, 'errors' => 2, 'throttled' => 1, 'flagged' => 2,
    ]);

    $routes = collect($traffic->routes('1h'))->keyBy('route');
    expect($routes['api.plugin.heartbeat'])->toMatchArray(['requests' => 19, 'errors' => 0, 'p95_ms' => 25])
        ->and($routes['api.plugin.bank'])->toMatchArray(['requests' => 2, 'errors' => 2, 'error_rate' => 1.0, 'throttled' => 1]);

    expect($traffic->topUsers('1h')[0])->toMatchArray([
        'user_id' => $busy->id, 'name' => 'Traffic User', 'requests' => 20, 'errors' => 2, 'throttled' => 1, 'hashes' => 2,
    ]);

    expect($traffic->flagCounts('1h', $busy->id))->toBe(['throttled' => 1, 'validation_failed' => 1])
        ->and($traffic->summary('1h', $quiet->id)['requests'])->toBe(1)
        ->and($traffic->timeline('1h'))->toHaveCount(12)
        ->and(array_sum(array_column($traffic->timeline('1h'), 'requests')))->toBe(21);

    $identities = $traffic->identities('1h', $busy->id);
    expect(array_column($identities['hashes'], 'account_hash'))->toBe(['hash-a', 'hash-b'])
        ->and($identities['ips'][0])->toMatchArray(['ip' => '10.0.0.1', 'requests' => 20]);

    $feed = $traffic->requests('1h', ['flagged' => true, 'flag' => 'throttled']);
    expect($feed->total())->toBe(1)
        ->and($feed->items()[0])->toMatchArray(['user_name' => 'Traffic User', 'status' => 429, 'flags' => ['throttled']]);
});

it('lists suspicious users and anonymous IPs with their reasons', function () {
    $hijacker = trafficUser('hijack@test.local');
    $sharer = trafficUser('share@test.local');
    $clean = trafficUser('clean@test.local');

    foreach (range(1, PluginTraffic::MIN_FLAGGED_EVENTS) as $i) {
        trafficLog(['user_id' => $hijacker->id, 'account_hash' => 'hash-x', 'status' => 403, 'flags' => ['account_conflict']]);
    }
    trafficLog(['user_id' => $sharer->id, 'account_hash' => 'hash-x']);
    // One short of the threshold for each single-event rule: not listed.
    $unlucky = trafficUser('unlucky@test.local');
    foreach (range(1, PluginTraffic::MIN_FLAGGED_EVENTS - 1) as $i) {
        trafficLog(['user_id' => $unlucky->id, 'account_hash' => 'hash-unlucky', 'status' => 429, 'flags' => ['throttled']]);
        trafficLog(['user_id' => $unlucky->id, 'account_hash' => 'hash-unlucky', 'flags' => ['oversized']]);
        trafficLog(['user_id' => $unlucky->id, 'account_hash' => 'hash-unlucky', 'status' => 403, 'flags' => ['account_conflict']]);
    }
    foreach (range(1, 6) as $i) {
        trafficLog(['user_id' => $sharer->id, 'account_hash' => "hash-{$i}"]);
    }
    foreach (range(1, 20) as $i) {
        trafficLog(['user_id' => $clean->id, 'account_hash' => 'hash-clean']);
    }
    foreach (range(1, PluginTraffic::MAX_ANONYMOUS_FAILURES) as $i) {
        trafficLog(['route' => 'api.login', 'ip' => '6.6.6.6', 'status' => 401, 'flags' => ['unauthenticated'], 'login_email_hash' => hash('sha256', "victim{$i}@test.local")]);
    }

    $suspicious = collect(app(PluginTraffic::class)->suspiciousUsers('1h'));

    expect($suspicious->pluck('user_id')->all())->not->toContain($clean->id)->not->toContain($unlucky->id)
        ->and($suspicious->firstWhere('user_id', $hijacker->id)['reasons'])->toBe([
            '5 attempts to push for an account linked to another user',
            'Shares account hash hash-x… with 1 other user',
        ])
        ->and($suspicious->firstWhere('user_id', $sharer->id)['reasons'])->toBe([
            'Sent 7 different account hashes',
            'Shares account hash hash-x… with 1 other user',
        ])
        ->and($suspicious->firstWhere('ip', '6.6.6.6')['reasons'])->toBe([
            '10 unauthenticated requests',
            'Tried 10 login emails',
        ]);
});

it('keeps the traffic monitor from members without manage instance', function () {
    SettingHelper::setSetting('instance_configured', true, 'bool');
    $user = trafficUser();
    $user->forceFill(['email_verified_at' => now()])->save();

    $this->actingAs($user)->get(route('admin.traffic'))->assertForbidden();
    $this->get(route('admin.traffic.user', $user))->assertForbidden();
});

it('shows the traffic monitor to instance managers', function () {
    SettingHelper::setSetting('instance_configured', true, 'bool');
    $admin = adminUser();
    foreach (range(1, PluginTraffic::MIN_FLAGGED_EVENTS) as $i) {
        trafficLog(['user_id' => $admin->id, 'status' => 429, 'flags' => ['throttled']]);
    }

    $this->actingAs($admin)->get(route('admin.traffic', ['window' => '1h', 'flag' => 'throttled']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Traffic/Index')
            ->where('window', '1h')
            ->where('filters.flag', 'throttled')
            ->where('summary.requests', PluginTraffic::MIN_FLAGGED_EVENTS)
            ->has('timeline', 12)
            ->has('routes', 1)
            ->has('topUsers', 1)
            ->has('suspicious', 1)
            ->has('flagged.data', PluginTraffic::MIN_FLAGGED_EVENTS));

    $this->actingAs($admin)->get(route('admin.traffic', ['window' => 'forever', 'flag' => 'nonsense']))
        ->assertInertia(fn (Assert $page) => $page->where('window', '24h')->where('filters.flag', null));
});

it('shows one user\'s plugin traffic', function () {
    SettingHelper::setSetting('instance_configured', true, 'bool');
    $admin = adminUser();
    $user = trafficUser();
    trafficLog(['user_id' => $user->id, 'account_hash' => 'hash-a', 'username' => 'Zlimon']);
    trafficLog(['user_id' => $admin->id]);

    $this->actingAs($admin)->get(route('admin.traffic.user', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Traffic/Show')
            ->where('user.id', $user->id)
            ->where('summary.requests', 1)
            ->where('identities.hashes.0.usernames', ['Zlimon'])
            ->has('requests.data', 1));
});
