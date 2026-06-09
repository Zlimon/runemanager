<?php

use App\Services\Accounts\GroupIronmanValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;

beforeEach(fn () => Cache::flush());

function validatorWith(array $responses): GroupIronmanValidator
{
    $client = new Client(['handler' => HandlerStack::create(new MockHandler($responses))]);

    return new GroupIronmanValidator($client);
}

it('returns true for a Group Ironman account', function () {
    $validator = validatorWith([
        new Response(200, [], json_encode(['data' => ['Username' => 'X', 'Game mode' => 1, 'GIM' => 1]])),
    ]);

    expect($validator->isGroupIronman('Gim Guy'))->toBeTrue();
});

it('returns false for a non-GIM account', function () {
    $validator = validatorWith([
        new Response(200, [], json_encode(['data' => ['Username' => 'X', 'Game mode' => 1, 'GIM' => 0]])),
    ]);

    expect($validator->isGroupIronman('Iron Solo'))->toBeFalse();
});

it('returns false when the player is not tracked on TempleOSRS', function () {
    $validator = validatorWith([
        new Response(200, [], json_encode(['error' => ['Code' => 402, 'Message' => 'User not found']])),
    ]);

    expect($validator->isGroupIronman('Unknown'))->toBeFalse();
});

it('fails closed on a network error', function () {
    $validator = validatorWith([
        new ConnectException('connection refused', new Psr7Request('GET', 'player_info.php')),
    ]);

    expect($validator->isGroupIronman('Offline'))->toBeFalse();
});

it('caches the result so it does not hit the API twice', function () {
    // Only one response is queued; a second HTTP call would error out (and read
    // as false), so a second `true` proves the cache served it.
    $validator = validatorWith([
        new Response(200, [], json_encode(['data' => ['GIM' => 1]])),
    ]);

    expect($validator->isGroupIronman('Cacheme'))->toBeTrue();
    expect($validator->isGroupIronman('Cacheme'))->toBeTrue();
});
