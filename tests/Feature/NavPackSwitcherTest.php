<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/**
 * The `my_packs` / `pack_preference` shared props behind the quick pack
 * switcher in the account nav dropdown. `makePack()` and `freshPackUser()` are
 * in tests/Pest.php, shared with UserResourcePackTest.
 */
it('shares only the packs the viewer installed themselves', function () {
    $user = freshPackUser();
    $other = freshPackUser('other@test.local');

    makePack('sample-vanilla');
    makePack('mine', ['installed_by_user_id' => $user->id]);
    makePack('theirs', ['installed_by_user_id' => $other->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('my_packs', 1)
            ->where('my_packs.0.name', 'mine'),
        );
});

it('includes the pack the viewer currently has selected even if someone else installed it', function () {
    $user = freshPackUser();
    $other = freshPackUser('other@test.local');

    $theirs = makePack('theirs', ['installed_by_user_id' => $other->id]);
    $user->forceFill(['resource_pack_id' => $theirs->id])->save();

    // Otherwise the switcher could not show what is actually in effect, and the
    // member would have no way back to it from the dropdown.
    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('my_packs', 1)
            ->where('my_packs.0.name', 'theirs'),
        );
});

it('leaves out a pack whose files are still being fetched', function () {
    $user = freshPackUser();

    makePack('ready', ['installed_by_user_id' => $user->id]);
    // A stub row from a queued install: selecting it would strip the interface
    // bare until the job finished.
    makePack('queued', ['installed_by_user_id' => $user->id, 'version' => 'pending']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('my_packs', 1)
            ->where('my_packs.0.name', 'ready'),
        );
});

it('reports following the instance default as no pack preference', function () {
    $user = freshPackUser();
    makePack('sample-vanilla');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('pack_preference.id', null)
            ->where('pack_preference.disabled', false),
        );
});

it('distinguishes a chosen pack from the instance default that happens to match it', function () {
    $user = freshPackUser();
    $pack = makePack('midnight', ['installed_by_user_id' => $user->id]);

    $user->forceFill(['resource_pack_id' => $pack->id])->save();

    // `pack` (the pack in effect) would read the same either way; only the
    // stored preference tells the switcher which row to mark.
    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('pack_preference.id', $pack->id)
            ->where('pack_preference.disabled', false),
        );
});

it('reports the opt-out so the switcher can mark "No resource pack"', function () {
    $user = freshPackUser();
    makePack('sample-vanilla');

    $user->forceFill(['disable_resource_pack' => true])->save();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('pack_preference.disabled', true),
        );
});

it('gives guests no switcher at all', function () {
    makePack('sample-vanilla');
    makePack('mine', ['installed_by_user_id' => freshPackUser()->id]);

    // The feed is public, so a guest reaches a page carrying the shared props.
    // Guests keep the GuestPackPicker instead: they have no row to store a
    // per-pack preference on.
    $this->get(route('feed.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('my_packs', 0)
            ->where('pack_preference', null),
        );
});

it('switches the viewer to one of their packs through the shared endpoint', function () {
    $user = freshPackUser();
    $pack = makePack('midnight', ['installed_by_user_id' => $user->id]);

    $this->actingAs($user)
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $pack->id])
        ->assertOk();

    expect($user->fresh()->resource_pack_id)->toBe($pack->id);
});
