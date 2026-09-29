<?php

use App\Support\Experience;

it('maps experience to levels on the OSRS table', function (int $xp, int $level) {
    expect(Experience::levelFor($xp))->toBe($level);
})->with([
    'zero' => [0, 1],
    'just below level 2' => [82, 1],
    'level 2' => [83, 2],
    'level 50' => [101_333, 50],
    'one short of 92' => [6_517_252, 91],
    'level 92' => [6_517_253, 92],
    'level 99' => [13_034_431, 99],
    'capped at 99' => [200_000_000, 99],
]);

it('gives the experience a level starts at', function () {
    expect(Experience::forLevel(1))->toBe(0)
        ->and(Experience::forLevel(70))->toBe(737_627)
        ->and(Experience::forLevel(99))->toBe(13_034_431)
        ->and(Experience::forLevel(120))->toBe(13_034_431);
});
