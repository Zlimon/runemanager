<?php

use App\Models\Account;
use App\Models\Loot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(fn () => Loot::query()->delete());

it('groups unique loot sources into category sections', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create();

    Loot::create(['account_id' => $account->id, 'source' => 'Vorkath', 'type' => 'NPC', 'items' => [], 'total_value' => 1_000_000, 'killed_at' => now()]);
    Loot::create(['account_id' => $account->id, 'source' => 'Goblin', 'type' => 'NPC', 'items' => [], 'total_value' => 10, 'killed_at' => now()]);
    Loot::create(['account_id' => $account->id, 'source' => 'Barrows', 'type' => 'EVENT', 'items' => [], 'total_value' => 500_000, 'killed_at' => now()]);
    Loot::create(['account_id' => $account->id, 'source' => 'Man', 'type' => 'PICKPOCKET', 'items' => [], 'total_value' => 5, 'killed_at' => now()]);

    $this->actingAs($user)
        ->get(route('hiscores.loot.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Loot/Index')
            ->has('groups', 3)
            // Section order follows the category list, not value.
            ->where('groups.0.label', 'Monsters')
            ->where('groups.1.label', 'Events & Raids')
            ->where('groups.2.label', 'Pickpocketing')
            // Sources within a section are ranked by combined value.
            ->where('groups.0.sources.0.name', 'Vorkath')
            ->where('groups.0.sources.0.total_value', 1000000)
            ->where('groups.0.sources.1.name', 'Goblin'),
        );

    Loot::query()->delete();
});

it('files untyped loot under the Other section', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create();

    Loot::create(['account_id' => $account->id, 'source' => 'Mystery', 'items' => [], 'total_value' => 1, 'killed_at' => now()]);

    $this->actingAs($user)
        ->get(route('hiscores.loot.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Loot/Index')
            ->has('groups', 1)
            ->where('groups.0.label', 'Other')
            ->where('groups.0.sources.0.name', 'Mystery'),
        );

    Loot::query()->delete();
});

it('shows each account loot for a source ranked by value', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $rich = Account::factory()->for($user)->create(['username' => 'Rich']);
    $poor = Account::factory()->for($user)->create(['username' => 'Poor']);

    Loot::create(['account_id' => $rich->id, 'source' => 'Vorkath', 'type' => 'NPC', 'items' => [['id' => 536, 'quantity' => 40]], 'total_value' => 800_000, 'killed_at' => now()]);
    Loot::create(['account_id' => $rich->id, 'source' => 'Vorkath', 'type' => 'NPC', 'items' => [['id' => 536, 'quantity' => 10]], 'total_value' => 200_000, 'killed_at' => now()]);
    Loot::create(['account_id' => $poor->id, 'source' => 'Vorkath', 'type' => 'NPC', 'items' => [['id' => 995, 'quantity' => 5000]], 'total_value' => 5_000, 'killed_at' => now()]);
    Loot::create(['account_id' => $rich->id, 'source' => 'Zulrah', 'type' => 'NPC', 'items' => [['id' => 12934, 'quantity' => 1]], 'total_value' => 9_000_000, 'killed_at' => now()]);

    $this->actingAs($user)
        ->get(route('hiscores.loot.show', 'Vorkath'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Loot/Show')
            ->where('source', 'Vorkath')
            ->has('hiscores', 2)
            ->where('hiscores.0.account.username', 'Rich')
            ->where('hiscores.0.rank', 1)
            ->where('hiscores.0.total_value', 1000000)
            ->where('hiscores.0.drops', 2)
            // Quantities are summed across the account's drops of this source.
            ->where('hiscores.0.items.0.id', 536)
            ->where('hiscores.0.items.0.quantity', 50)
            ->where('hiscores.1.account.username', 'Poor'),
        );

    Loot::query()->delete();
});

it('404s for a source with no loot', function () {
    $user = User::factory()->withPersonalTeam()->create();

    $this->actingAs($user)
        ->get(route('hiscores.loot.show', 'Nonexistent'))
        ->assertNotFound();
});

it('requires authentication', function () {
    $this->get(route('hiscores.loot.index'))->assertRedirect(route('login'));
});
