<?php

use App\Services\Hiscores\GroupIronmanValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

function gimValidator(MockHandler $mock): GroupIronmanValidator
{
    return new GroupIronmanValidator(new Client(['handler' => HandlerStack::create($mock)]));
}

it('treats a normal group page as an existing group', function () {
    $validator = gimValidator(new MockHandler([
        new Response(200, [], '<title>Old School Group Ironman - Hiscores - ikeabanden</title>'),
    ]));

    expect($validator->exists('ikeabanden'))->toBeTrue();
});

it('detects a missing group from the not-found notice', function () {
    $validator = gimValidator(new MockHandler([
        new Response(200, [], "<p class='uc-scroll__no-results'>Unable to find group with name <strong>nope</strong>.</p>"),
    ]));

    expect($validator->exists('nope'))->toBeFalse();
});

it('returns null on a non-200 response so callers do not hard-block', function () {
    $validator = gimValidator(new MockHandler([new Response(503, [], 'down')]));

    expect($validator->exists('ikeabanden'))->toBeNull();
});

it('returns null when the request throws', function () {
    $validator = gimValidator(new MockHandler([
        new ConnectException('boom', new Request('GET', 'x')),
    ]));

    expect($validator->exists('ikeabanden'))->toBeNull();
});

it('returns null for a blank name without making a request', function () {
    // Empty MockHandler queue: any HTTP attempt would throw, proving none is made.
    expect(gimValidator(new MockHandler([]))->exists('  '))->toBeNull();
});
