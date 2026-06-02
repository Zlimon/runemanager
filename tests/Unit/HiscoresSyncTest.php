<?php

use App\Services\Feed\RecordFeedEvent;
use App\Services\Hiscores\HiscoresSync;
use App\Services\Hiscores\OsrsHiscoresClient;

beforeEach(function () {
    $this->sync = new HiscoresSync(new OsrsHiscoresClient, new RecordFeedEvent);
});

it('normalises skills and activities into a snake_keyed jsonb shape', function () {
    $payload = [
        'skills' => [
            ['id' => 0, 'name' => 'Overall', 'rank' => 12345, 'level' => 1500, 'xp' => 99999999],
            ['id' => 1, 'name' => 'Attack', 'rank' => 500, 'level' => 99, 'xp' => 200000000],
        ],
        'activities' => [
            ['id' => 0, 'name' => 'League Points', 'rank' => 1, 'score' => 5000],
            ['id' => 17, 'name' => 'Theatre of Blood: Hard Mode', 'rank' => 42, 'score' => 100],
        ],
    ];

    $entries = $this->sync->normalise($payload);

    expect($entries['skills'])->toHaveKeys(['overall', 'attack']);
    expect($entries['skills']['attack'])->toBe(['rank' => 500, 'level' => 99, 'xp' => 200000000]);

    expect($entries['activities'])->toHaveKeys(['league_points', 'theatre_of_blood_hard_mode']);
    expect($entries['activities']['theatre_of_blood_hard_mode'])->toBe(['rank' => 42, 'score' => 100]);
});

it('preserves unranked sentinels (-1) verbatim', function () {
    $payload = [
        'skills' => [
            ['id' => 1, 'name' => 'Attack', 'rank' => -1, 'level' => 1, 'xp' => -1],
        ],
        'activities' => [],
    ];

    $entries = $this->sync->normalise($payload);

    expect($entries['skills']['attack'])->toBe(['rank' => -1, 'level' => 1, 'xp' => -1]);
});

it('ignores entries with missing name and tolerates missing skills or activities arrays', function () {
    $entries = $this->sync->normalise([
        'skills' => [
            ['id' => 999, 'rank' => 1, 'level' => 1, 'xp' => 0],
            ['id' => 1, 'name' => 'Attack', 'rank' => 1, 'level' => 99, 'xp' => 1],
        ],
    ]);

    expect($entries['skills'])->toHaveCount(1)->toHaveKey('attack');
    expect($entries['activities'])->toBe([]);
});

it('absorbs new skill/activity entries without code changes', function () {
    $entries = $this->sync->normalise([
        'skills' => [
            ['id' => 999, 'name' => 'Sailing', 'rank' => 1, 'level' => 50, 'xp' => 100000],
        ],
        'activities' => [
            ['id' => 999, 'name' => 'Brand New Boss', 'rank' => 5, 'score' => 12],
        ],
    ]);

    expect($entries['skills'])->toHaveKey('sailing');
    expect($entries['activities'])->toHaveKey('brand_new_boss');
});
