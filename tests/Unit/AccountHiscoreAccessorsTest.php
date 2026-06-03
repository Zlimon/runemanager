<?php

use App\Models\Account;
use App\Models\AccountHiscore;

/**
 * Builds an Account with an in-memory hiscore relation so the boss/clue
 * accessors can be exercised without touching the database.
 */
function accountWithActivities(array $activities): Account
{
    $account = new Account;
    $hiscore = new AccountHiscore(['entries' => ['skills' => [], 'activities' => $activities]]);
    $account->setRelation('hiscore', $hiscore);

    return $account;
}

it('keeps NPC bosses on the bosses tab', function () {
    $account = accountWithActivities([
        'abyssal_sire' => ['rank' => 1, 'score' => 500],
        'kreearra' => ['rank' => 2, 'score' => 300],
    ]);

    $slugs = $account->bosses->pluck('slug');

    expect($slugs)->toContain('abyssal_sire', 'kreearra');
});

it('filters minigame and chest activities off the bosses tab', function () {
    $account = accountWithActivities([
        'abyssal_sire' => ['rank' => 1, 'score' => 500],
        'lms_rank' => ['rank' => 1, 'score' => 100],
        'soul_wars_zeal' => ['rank' => 1, 'score' => 100],
        'barrows_chests' => ['rank' => 1, 'score' => 100],
        'bounty_hunter_hunter' => ['rank' => 1, 'score' => 100],
        'bounty_hunter_legacy_hunter' => ['rank' => 1, 'score' => 100],
        'bounty_hunter_legacy_rogue' => ['rank' => 1, 'score' => 100],
        'clue_scrolls_all' => ['rank' => 1, 'score' => 100],
    ]);

    $slugs = $account->bosses->pluck('slug');

    expect($slugs)->toContain('abyssal_sire')
        ->not->toContain('lms_rank')
        ->not->toContain('soul_wars_zeal')
        ->not->toContain('barrows_chests')
        ->not->toContain('bounty_hunter_hunter')
        ->not->toContain('bounty_hunter_legacy_hunter')
        ->not->toContain('bounty_hunter_legacy_rogue')
        ->not->toContain('clue_scrolls_all');
});

it('exposes every clue tier in order even with no kc', function () {
    $account = accountWithActivities([
        'clue_scrolls_all' => ['rank' => 5, 'score' => 42],
    ]);

    $clues = $account->clues;

    expect($clues->pluck('slug')->all())->toBe([
        'clue_scrolls_beginner',
        'clue_scrolls_easy',
        'clue_scrolls_medium',
        'clue_scrolls_hard',
        'clue_scrolls_elite',
        'clue_scrolls_master',
        'clue_scrolls_all',
    ]);

    expect($clues->firstWhere('slug', 'clue_scrolls_all'))
        ->toMatchArray(['score' => 42, 'rank' => 5]);

    expect($clues->firstWhere('slug', 'clue_scrolls_beginner'))
        ->toMatchArray(['score' => 0, 'rank' => 0]);
});
