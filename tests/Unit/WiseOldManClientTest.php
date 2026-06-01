<?php

use App\Services\WiseOldMan\WiseOldManClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;

it('falls back to the public WOM api when no url is configured', function () {
    Config::set('services.wise_old_man.url', null);

    $client = new WiseOldManClient;

    expect($client->url)->toBe('https://api.wiseoldman.net/v2');
});

it('uses the configured user agent', function () {
    Config::set('services.wise_old_man.user_agent', 'RuneManager-Test/9.9');

    expect((new WiseOldManClient)->userAgent)->toBe('RuneManager-Test/9.9');
});

it('builds the right URL + query + headers for recentNameChanges', function () {
    Config::set('services.wise_old_man.url', 'https://wom.example/v2');
    Config::set('services.wise_old_man.user_agent', 'RuneManager-Test/9.9');

    $sent = [];
    $mock = new MockHandler([new Response(200, [], '[]')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($sent));

    $client = new WiseOldManClient(new HttpClient(['handler' => $stack]));
    $client->recentNameChanges('approved', 50, 100);

    $request = $sent[0]['request'];

    expect($request->getUri()->getPath())->toBe('/v2/names');

    parse_str($request->getUri()->getQuery(), $query);
    expect($query)->toBe(['status' => 'approved', 'limit' => '50', 'offset' => '100']);

    expect($request->getHeaderLine('User-Agent'))->toBe('RuneManager-Test/9.9');
    expect($request->getHeaderLine('Accept'))->toBe('application/json');
});
