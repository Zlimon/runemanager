<?php

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

function overallHiscore(Account $account, int $rank, int $level, int $xp): void
{
    AccountHiscore::create([
        'account_id' => $account->id,
        'entries' => ['skills' => ['overall' => ['rank' => $rank, 'level' => $level, 'xp' => $xp]], 'activities' => []],
        'fetched_at' => now(),
    ]);
}

it('ranks accounts by overall (total level then total XP)', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $top = Account::factory()->for($user)->create(['username' => 'Maxed']);
    $mid = Account::factory()->for($user)->create(['username' => 'Mid']);
    $unranked = Account::factory()->for($user)->create(['username' => 'Fresh']);

    overallHiscore($top, 100, 2277, 4_600_000_000);
    overallHiscore($mid, 5000, 1500, 100_000_000);
    overallHiscore($unranked, 0, 0, 0); // never on the hiscores

    $this->actingAs($user)
        ->get(route('hiscores.overall.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Skills/Show')
            ->where('skillName', 'Overall')
            ->where('skillSlug', 'overall')
            ->has('hiscores', 3)
            ->where('hiscores.0.account.username', 'Maxed')
            ->where('hiscores.0.level', 2277)
            ->where('hiscores.0.xp', 4600000000)
            ->where('hiscores.1.account.username', 'Mid')
            ->where('hiscores.2.account.username', 'Fresh'),
        );
});

it('requires authentication', function () {
    $this->get(route('hiscores.overall.index'))->assertRedirect(route('login'));
});
