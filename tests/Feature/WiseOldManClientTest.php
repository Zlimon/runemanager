<?php

use App\Services\WiseOldMan\WiseOldManClient;
use App\Services\WiseOldMan\WiseOldManRateLimited;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
    config([
        'services.wiseoldman.url' => 'https://api.wiseoldman.net/v2',
        'services.wiseoldman.api_key' => null,
        'services.wiseoldman.user_agent' => 'RuneManager (+https://runemanager.com)',
    ]);
});

it('standardises usernames the way Wise Old Man does', function () {
    expect(WiseOldManClient::standardise('  Hey_Jase '))->toBe('hey jase')
        ->and(WiseOldManClient::standardise('Kill-The--GE'))->toBe('kill the ge');
});

it('fetches a player with the user agent and no api key by default', function () {
    Http::fake(['api.wiseoldman.net/v2/players/bartheez' => Http::response(womFixture('player-bartheez'))]);

    $player = app(WiseOldManClient::class)->player('Bartheez');

    expect($player['id'])->toBe(315771)
        ->and($player['displayName'])->toBe('Bartheez');

    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && $request->hasHeader('User-Agent', 'RuneManager (+https://runemanager.com)')
        && ! $request->hasHeader('x-api-key'));
});

it('url-encodes the standardised name and sends the api key when configured', function () {
    config(['services.wiseoldman.api_key' => 'secret']);
    Http::fake(['api.wiseoldman.net/v2/players/hey%20jase' => Http::response(womFixture('player-bartheez'))]);

    app(WiseOldManClient::class)->player('Hey_Jase');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.wiseoldman.net/v2/players/hey%20jase'
        && $request->hasHeader('x-api-key', 'secret'));
});

it('returns null for a player Wise Old Man does not track', function () {
    Http::fake(['api.wiseoldman.net/*' => Http::response(['message' => 'Player not found.'], 404)]);

    expect(app(WiseOldManClient::class)->player('nobody here'))->toBeNull();
});

it('throws with the Retry-After delay on a 429', function () {
    Http::fake(['api.wiseoldman.net/*' => Http::response(['message' => 'Too many requests.'], 429, ['Retry-After' => '42'])]);

    try {
        app(WiseOldManClient::class)->player('Bartheez');
        $this->fail('Expected WiseOldManRateLimited.');
    } catch (WiseOldManRateLimited $e) {
        expect($e->retryAfter)->toBe(42);
    }
});

it('stops before sending once the per-minute budget is spent', function () {
    Http::fake(['api.wiseoldman.net/*' => Http::response(womFixture('player-bartheez'))]);
    $client = app(WiseOldManClient::class);

    for ($i = 0; $i < WiseOldManClient::perMinute(); $i++) {
        $client->player('Bartheez');
    }

    expect(fn () => $client->player('Bartheez'))->toThrow(WiseOldManRateLimited::class);
    Http::assertSentCount(18);
});

it('allows a larger budget with an api key', function () {
    config(['services.wiseoldman.api_key' => 'secret']);

    expect(WiseOldManClient::perMinute())->toBe(90);
});

it('posts an update and returns null when the name is not on the hiscores', function () {
    Http::fake(['api.wiseoldman.net/v2/players/zzz' => Http::response(['message' => 'Failed to load hiscores.'], 400)]);

    expect(app(WiseOldManClient::class)->update('zzz'))->toBeNull();

    Http::assertSent(fn (Request $request) => $request->method() === 'POST');
});

it('pages through a player\'s snapshots', function () {
    Http::fake(['api.wiseoldman.net/v2/players/bartheez/snapshots*' => Http::response(womFixture('snapshots-bartheez'))]);

    $snapshots = app(WiseOldManClient::class)->snapshots('Bartheez', 'year', 200, 400);

    expect($snapshots)->toHaveCount(4)
        ->and($snapshots[0]['data'])->toHaveKeys(['skills', 'bosses', 'activities', 'computed']);

    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && $request['period'] === 'year' && (int) $request['limit'] === 200 && (int) $request['offset'] === 400);
});

it('fetches a player\'s achievements and records', function () {
    Http::fake([
        'api.wiseoldman.net/v2/players/bartheez/achievements' => Http::response(womFixture('achievements-bartheez')),
        'api.wiseoldman.net/v2/players/bartheez/records' => Http::response(womFixture('records-bartheez')),
    ]);
    $client = app(WiseOldManClient::class);

    expect($client->achievements('Bartheez')[0])->toHaveKeys(['name', 'metric', 'measure', 'threshold', 'createdAt', 'accuracy', 'legacy'])
        ->and($client->records('Bartheez')[0])->toHaveKeys(['period', 'metric', 'value', 'updatedAt']);
});

it('returns empty lists for a player Wise Old Man does not track', function () {
    Http::fake(['api.wiseoldman.net/*' => Http::response(['message' => 'Player not found.'], 404)]);
    $client = app(WiseOldManClient::class);

    expect($client->snapshots('nobody', 'year'))->toBe([])
        ->and($client->achievements('nobody'))->toBe([])
        ->and($client->records('nobody'))->toBe([]);
});

it('gives interactive callers a shorter timeout without changing the shared client', function () {
    $client = app(WiseOldManClient::class);
    $interactive = $client->interactive();

    $timeout = fn (WiseOldManClient $c): int => (fn () => $this->timeout)->call($c);

    expect($interactive)->not->toBe($client)
        ->and($timeout($interactive))->toBe(5)
        ->and($timeout($client))->toBe(20);
});
