<?php

use App\Services\WiseOldMan\WomMetrics;

it('lists every metric a real snapshot carries, in WOM order', function () {
    $data = womFixture('snapshots-bartheez')[0]['data'];

    expect(WomMetrics::SKILLS)->toBe(array_keys($data['skills']))
        ->and(WomMetrics::BOSSES)->toBe(array_keys($data['bosses']))
        ->and(WomMetrics::ACTIVITIES)->toBe(array_keys($data['activities']));
});

it('groups metrics and rejects unknown ones', function () {
    expect(WomMetrics::group('attack'))->toBe('skill')
        ->and(WomMetrics::group('overall'))->toBe('skill')
        ->and(WomMetrics::group('zulrah'))->toBe('boss')
        ->and(WomMetrics::group('clue_scrolls_all'))->toBe('activity')
        ->and(WomMetrics::group('ehp'))->toBeNull()
        ->and(WomMetrics::isValid('zulrah'))->toBeTrue()
        ->and(WomMetrics::isValid('nope'))->toBeFalse();
});

it('maps metrics onto snapshot document fields', function () {
    expect(WomMetrics::path('overall'))->toBe('exp')
        ->and(WomMetrics::path('attack'))->toBe('skills.attack')
        ->and(WomMetrics::path('zulrah'))->toBe('bosses.zulrah')
        ->and(WomMetrics::path('last_man_standing'))->toBe('activities.last_man_standing')
        ->and(fn () => WomMetrics::path('nope'))->toThrow(InvalidArgumentException::class);
});

it('labels metrics', function () {
    expect(WomMetrics::label('runecrafting'))->toBe('Runecraft')
        ->and(WomMetrics::label('clue_scrolls_all'))->toBe('Clue Scrolls (All)')
        ->and(WomMetrics::label('chambers_of_xeric'))->toBe('Chambers of Xeric')
        ->and(WomMetrics::label('the_corrupted_gauntlet'))->toBe('The Corrupted Gauntlet')
        ->and(WomMetrics::label('kreearra'))->toBe("Kree'Arra")
        ->and(WomMetrics::label('attack'))->toBe('Attack');
});

it('translates between WOM metric names and our slugs', function () {
    expect(WomMetrics::localSlug('runecrafting'))->toBe('runecraft')
        ->and(WomMetrics::localSlug('attack'))->toBe('attack')
        ->and(WomMetrics::fromLocalSlug('runecraft'))->toBe('runecrafting')
        ->and(WomMetrics::fromLocalSlug('zulrah'))->toBe('zulrah')
        // Hiscore activity keys that WOM names differently.
        ->and(WomMetrics::fromLocalSlug('lms_rank'))->toBe('last_man_standing')
        ->and(WomMetrics::fromLocalSlug('rifts_closed'))->toBe('guardians_of_the_rift')
        ->and(WomMetrics::fromLocalSlug('tombs_of_amascut_expert_mode'))->toBe('tombs_of_amascut_expert');
});

it('turns a snapshot into maps without overall and unranked values', function () {
    $data = womFixture('snapshots-bartheez')[0]['data'];
    $data['bosses']['zulrah']['kills'] = -1;

    $maps = WomMetrics::snapshotMaps($data);

    expect($maps['skills'])->toHaveCount(24)->not->toHaveKey('overall')
        ->and($maps['skills']['attack'])->toBe(41239184)
        ->and($maps['bosses'])->toHaveCount(70)->not->toHaveKey('zulrah')
        ->and($maps['activities'])->toHaveCount(15)
        ->and(WomMetrics::snapshotMaps([]))->toBe(['skills' => [], 'bosses' => [], 'activities' => []]);
});

it('resolves icons for skills, bosses, clues and minigames', function () {
    expect(WomMetrics::icon('runecrafting'))->toBe(['kind' => 'skill', 'slug' => 'runecraft'])
        ->and(WomMetrics::icon('overall'))->toBe(['kind' => 'skill', 'slug' => 'overall'])
        ->and(WomMetrics::icon('zulrah'))->toBe(['src' => '/images/boss/zulrah.png'])
        ->and(WomMetrics::icon('tombs_of_amascut_expert'))->toBe(['src' => '/images/boss/tombs-of-amascut-expert-mode.png'])
        ->and(WomMetrics::icon('clue_scrolls_hard'))->toBe(['src' => '/images/clue/hard-treasure-trails.png'])
        ->and(WomMetrics::icon('last_man_standing'))->toBe(['src' => '/images/boss/last-man-standing.png'])
        ->and(WomMetrics::icon('ehp'))->toBeNull();
});

it('has an existing image for every boss and clue icon it returns', function () {
    foreach ([...WomMetrics::BOSSES, ...WomMetrics::ACTIVITIES] as $metric) {
        $icon = WomMetrics::icon($metric);

        if ($icon !== null) {
            expect(public_path($icon['src']))->toBeFile();
        }
    }
});
