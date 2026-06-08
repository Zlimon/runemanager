<?php

use App\Models\Account;
use App\Models\Loot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(fn () => Loot::query()->delete());

it('ranks accounts by total loot value, newest rank first', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $rich = Account::factory()->for($user)->create(['username' => 'Rich']);
    $poor = Account::factory()->for($user)->create(['username' => 'Poor']);

    Loot::create(['account_id' => $rich->id, 'source' => 'Zulrah', 'items' => [], 'total_value' => 1_000_000, 'killed_at' => now()]);
    Loot::create(['account_id' => $rich->id, 'source' => 'Vorkath', 'items' => [], 'total_value' => 500_000, 'killed_at' => now()]);
    Loot::create(['account_id' => $poor->id, 'source' => 'Goblin', 'items' => [], 'total_value' => 10, 'killed_at' => now()]);

    $this->actingAs($user)
        ->get(route('hiscores.loot.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Loot/Show')
            ->where('selectedSource', null)
            ->where('sources', ['Zulrah', 'Vorkath', 'Goblin'])
            ->has('hiscores', 2)
            ->where('hiscores.0.account.username', 'Rich')
            ->where('hiscores.0.rank', 1)
            ->where('hiscores.0.total_value', 1500000)
            ->where('hiscores.0.drops', 2)
            ->where('hiscores.1.account.username', 'Poor')
            ->where('hiscores.1.rank', 2),
        );

    Loot::query()->delete();
});

it('narrows the leaderboard to a single source', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $hunter = Account::factory()->for($user)->create(['username' => 'Hunter']);
    $other = Account::factory()->for($user)->create(['username' => 'Other']);

    Loot::create(['account_id' => $hunter->id, 'source' => 'Vorkath', 'items' => [], 'total_value' => 800_000, 'killed_at' => now()]);
    Loot::create(['account_id' => $hunter->id, 'source' => 'Zulrah', 'items' => [], 'total_value' => 5_000_000, 'killed_at' => now()]);
    Loot::create(['account_id' => $other->id, 'source' => 'Vorkath', 'items' => [], 'total_value' => 200_000, 'killed_at' => now()]);

    $this->actingAs($user)
        ->get(route('hiscores.loot.index', ['source' => 'Vorkath']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Loot/Show')
            ->where('selectedSource', 'Vorkath')
            ->has('hiscores', 2)
            ->where('hiscores.0.account.username', 'Hunter')
            ->where('hiscores.0.total_value', 800000)
            ->where('hiscores.1.account.username', 'Other')
            ->where('hiscores.1.total_value', 200000),
        );

    Loot::query()->delete();
});

it('requires authentication', function () {
    $this->get(route('hiscores.loot.index'))->assertRedirect(route('login'));
});
