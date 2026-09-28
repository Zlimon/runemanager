<?php

use App\Models\Account;
use App\Models\WomAchievement;
use App\Models\WomPlayer;
use App\Models\WomRecord;
use App\Services\WiseOldMan\WomAccountView;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has no summary before the account was ever looked up', function () {
    expect(app(WomAccountView::class)->summary(Account::factory()->create()))->toBeNull();
});

it('summarises a linked profile with its wiseoldman.net link', function () {
    $account = Account::factory()->create(['username' => 'Iron Bart']);
    WomPlayer::factory()->for($account)->create(['ehp' => 812.5, 'status' => 'active']);

    $summary = app(WomAccountView::class)->summary($account);

    expect($summary['linked'])->toBeTrue()
        ->and($summary['not_found'])->toBeFalse()
        ->and($summary['ehp'])->toBe(812.5)
        ->and($summary['url'])->toBe('https://wiseoldman.net/players/iron%20bart');
});

it('marks a profile WOM does not know as not found, without a link', function () {
    $account = Account::factory()->create();
    WomPlayer::factory()->notFound()->for($account)->create();

    $summary = app(WomAccountView::class)->summary($account);

    expect($summary['linked'])->toBeFalse()
        ->and($summary['not_found'])->toBeTrue()
        ->and($summary['url'])->toBeNull();
});

it('groups personal records by metric in catalogue order, without EHP/EHB', function () {
    $account = Account::factory()->create();
    $at = now()->subDays(3)->startOfMinute();
    WomRecord::factory()->for($account)->kills('zulrah', 150)->period('week')->create(['achieved_at' => $at]);
    WomRecord::factory()->for($account)->period('day')->create(['metric' => 'attack', 'value' => 250_000.0, 'achieved_at' => $at]);
    WomRecord::factory()->for($account)->period('year')->create(['metric' => 'attack', 'value' => 9_000_000.0, 'achieved_at' => $at]);
    WomRecord::factory()->for($account)->period('week')->create(['metric' => 'ehp', 'value' => 12.5]);
    WomRecord::factory()->period('week')->create(['metric' => 'cooking']);

    $records = app(WomAccountView::class)->records($account);

    expect(array_column($records, 'metric'))->toBe(['attack', 'zulrah'])
        ->and($records[0])->toBe([
            'metric' => 'attack',
            'label' => 'Attack',
            'icon' => ['kind' => 'skill', 'slug' => 'attack'],
            'day' => ['value' => 250_000, 'achieved_at' => $at->toIso8601String()],
            'year' => ['value' => 9_000_000, 'achieved_at' => $at->toIso8601String()],
        ])
        ->and($records[1]['week']['value'])->toBe(150);
});

it('lists achievements newest first with undated ones last, flagging approximate and legacy rows', function () {
    $account = Account::factory()->create();
    $old = WomAchievement::factory()->for($account)->kills('zulrah', 100)->create(['achieved_at' => now()->subYear(), 'accuracy' => 1_000]);
    $undated = WomAchievement::factory()->for($account)->undated()->kills('vorkath', 50)->create();
    $recent = WomAchievement::factory()->for($account)->level99('cooking')->approximate()->create(['achieved_at' => now()->subDay()]);
    $legacy = WomAchievement::factory()->for($account)->legacy()->create(['achieved_at' => now()->subMonth()]);
    WomAchievement::factory()->create();

    $achievements = app(WomAccountView::class)->achievements($account);

    expect(array_column($achievements, 'name'))->toBe([$recent->name, $legacy->name, $old->name, $undated->name])
        ->and($achievements[0])->toMatchArray([
            'metric' => 'cooking',
            'measure' => 'experience',
            'threshold' => 13_034_431,
            'approximate' => true,
            'legacy' => false,
            'icon' => ['kind' => 'skill', 'slug' => 'cooking'],
        ])
        ->and($achievements[1]['legacy'])->toBeTrue()
        ->and($achievements[2]['approximate'])->toBeFalse()
        ->and($achievements[3])->toMatchArray(['achieved_at' => null, 'approximate' => false]);
});
