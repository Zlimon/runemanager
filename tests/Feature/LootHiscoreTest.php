<?php

use App\Models\Account;
use App\Models\Loot;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

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
            ->where('groups.0.sources.0.type', 'npc')
            ->where('groups.0.sources.0.total_value', 1000000)
            ->where('groups.0.sources.1.name', 'Goblin'),
        );

    Loot::query()->delete();
});

it('keeps same-named sources of different types as distinct entries', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create();

    // A pickpocketed "Man" and a killed "Man" share a name but are separate.
    Loot::create(['account_id' => $account->id, 'source' => 'Man', 'type' => 'PICKPOCKET', 'items' => [], 'total_value' => 78, 'killed_at' => now()]);
    Loot::create(['account_id' => $account->id, 'source' => 'Man', 'type' => 'NPC', 'items' => [], 'total_value' => 111, 'killed_at' => now()]);

    $this->actingAs($user)
        ->get(route('hiscores.loot.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Loot/Index')
            ->has('groups', 2)
            ->where('groups.0.label', 'Monsters')
            ->where('groups.0.sources.0.name', 'Man')
            ->where('groups.0.sources.0.type', 'npc')
            ->where('groups.1.label', 'Pickpocketing')
            ->where('groups.1.sources.0.name', 'Man')
            ->where('groups.1.sources.0.type', 'pickpocket'),
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
            ->where('groups.0.sources.0.name', 'Mystery')
            ->where('groups.0.sources.0.type', 'other'),
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
        ->get(route('hiscores.loot.show', ['npc', 'Vorkath']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Loot/Show')
            ->where('source', 'Vorkath')
            ->where('category', 'Monsters')
            ->has('hiscores.data', 2)
            ->where('hiscores.data.0.account.username', 'Rich')
            ->where('hiscores.data.0.rank', 1)
            ->where('hiscores.data.0.total_value', 1000000)
            ->where('hiscores.data.0.drops', 2)
            // Quantities are summed across the account's drops of this source.
            ->where('hiscores.data.0.items.0.id', 536)
            ->where('hiscores.data.0.items.0.quantity', 50)
            ->where('hiscores.data.1.account.username', 'Poor'),
        );

    Loot::query()->delete();
});

it('scopes the per-source board to the requested type', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create(['username' => 'Habski']);

    Loot::create(['account_id' => $account->id, 'source' => 'Man', 'type' => 'PICKPOCKET', 'items' => [['id' => 995, 'quantity' => 18]], 'total_value' => 78, 'killed_at' => now()]);
    Loot::create(['account_id' => $account->id, 'source' => 'Man', 'type' => 'NPC', 'items' => [['id' => 1205, 'quantity' => 1]], 'total_value' => 111, 'killed_at' => now()]);

    $this->actingAs($user)
        ->get(route('hiscores.loot.show', ['pickpocket', 'Man']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Loot/Show')
            ->where('source', 'Man')
            ->where('category', 'Pickpocketing')
            ->has('hiscores.data', 1)
            ->where('hiscores.data.0.total_value', 78)
            ->where('hiscores.data.0.drops', 1)
            // Only the pickpocket loot — the NPC-kill coins are excluded.
            ->has('hiscores.data.0.items', 1)
            ->where('hiscores.data.0.items.0.id', 995),
        );

    Loot::query()->delete();
});

it('404s for a source with no loot', function () {
    $user = User::factory()->withPersonalTeam()->create();

    $this->actingAs($user)
        ->get(route('hiscores.loot.show', ['npc', 'Nonexistent']))
        ->assertNotFound();
});

it('requires authentication', function () {
    $this->get(route('hiscores.loot.index'))->assertRedirect(route('login'));
});

/**
 * Records stored before the API normalised the type — in any casing other than
 * RuneLite's — were listed in the directory but 404'd on their own page, since
 * the page matches the uppercase type exactly.
 */
it('normalises stored loot types so every listed source is reachable', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create();

    Loot::create(['account_id' => $account->id, 'source' => 'Zulrah', 'type' => 'npc', 'items' => [], 'total_value' => 500, 'killed_at' => now()]);
    Loot::create(['account_id' => $account->id, 'source' => 'Vorkath', 'type' => 'NPC', 'items' => [], 'total_value' => 400, 'killed_at' => now()]);
    Loot::create(['account_id' => $account->id, 'source' => 'Goblin', 'type' => null, 'items' => [], 'total_value' => 5, 'killed_at' => now()]);

    // Before the migration the lowercase record cannot be found by its own URL.
    $this->actingAs($user)->get(route('hiscores.loot.show', ['npc', 'Zulrah']))->assertNotFound();

    (require database_path('migrations/2026_09_21_223647_normalize_loot_record_types.php'))->up();

    expect(Loot::query()->orderBy('total_value', 'desc')->pluck('type')->all())
        // Already-correct and untyped records are left exactly as they were.
        ->toBe(['NPC', 'NPC', null]);

    $this->actingAs($user)
        ->get(route('hiscores.loot.show', ['npc', 'Zulrah']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Hiscores/Loot/Show'));

    Loot::query()->delete();
});

it('files a normalised source under its real section rather than Other', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create();

    Loot::create(['account_id' => $account->id, 'source' => 'Zulrah', 'type' => 'npc', 'items' => [], 'total_value' => 500, 'killed_at' => now()]);

    (require database_path('migrations/2026_09_21_223647_normalize_loot_record_types.php'))->up();

    $this->actingAs($user)
        ->get(route('hiscores.loot.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('groups.0.label', 'Monsters')
            ->where('groups.0.sources.0.name', 'Zulrah'));

    Loot::query()->delete();
});
