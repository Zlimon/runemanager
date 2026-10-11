<?php

use App\Models\Account;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

/**
 * "RuneManager rank" is an account's place among this instance's accounts by
 * total XP — distinct from its official hiscores rank, which it used to repeat.
 */
function accountWithXp(int $xp): Account
{
    return Account::factory()->create(['xp' => $xp]);
}

it('ranks an account by total XP among the instance, highest first', function () {
    accountWithXp(300);
    $middle = accountWithXp(200);
    accountWithXp(100);

    expect($middle->instanceRank())->toBe(['position' => 2, 'total' => 3]);
});

it('gives accounts on equal XP the same position', function () {
    accountWithXp(500);
    $first = accountWithXp(200);
    $second = accountWithXp(200);
    $below = accountWithXp(100);

    // Standard competition order: 1, 2, 2, 4.
    expect($first->instanceRank()['position'])->toBe(2)
        ->and($second->instanceRank()['position'])->toBe(2)
        ->and($below->instanceRank()['position'])->toBe(4);
});

it('leaves an account unranked until it has XP to rank on', function () {
    accountWithXp(100);

    expect(accountWithXp(0)->instanceRank())->toBeNull();
});

it('does not count unsynced accounts towards the total', function () {
    $ranked = accountWithXp(100);
    accountWithXp(0);
    accountWithXp(0);

    // A fresh account with no hiscores must not turn "#1 of 1" into "#1 of 3".
    expect($ranked->instanceRank())->toBe(['position' => 1, 'total' => 1]);
});

it('passes the rank to Account Show', function () {
    $user = User::factory()->create();
    accountWithXp(900);
    $account = Account::factory()->for($user)->create(['xp' => 400]);

    $this->actingAs($user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Show')
            ->where('instanceRank', ['position' => 2, 'total' => 2]));
});
