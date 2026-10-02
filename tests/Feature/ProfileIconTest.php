<?php

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/*
 * The avatar icon is an OSRS item from the read-only catalogue, so these point
 * mongodb-static at a scratch database and seed it through the raw collection
 * (as CalendarEventTest does; Eloquent inserts would alias `id` to `_id`).
 */
beforeEach(function () {
    config([
        'database.connections.mongodb-static.dsn' => config('database.connections.mongodb.dsn'),
        'database.connections.mongodb-static.database' => 'gamedata_profile_icon_testing',
    ]);
    DB::purge('mongodb-static');
    Item::query()->delete();

    $collection = (new Item)->getConnection()->getDatabase()->selectCollection((new Item)->getTable());
    $collection->insertOne(['id' => '4151', 'name' => 'Abyssal whip', 'icon' => 'd2hpcA==']);
    $collection->insertOne(['id' => '995', 'name' => 'Coins', 'icon' => 'Y29pbnM=']);

    $this->user = User::factory()->withPersonalTeam()->create(['icon_id' => 995]);
});

it('hands the profile page the current icon item', function () {
    $this->actingAs($this->user)
        ->get('/user/profile')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Profile/Show')
            ->where('iconItem', ['id' => 995, 'name' => 'Coins', 'icon' => 'Y29pbnM=']));
});

it('lets the user pick another item as their icon', function () {
    $this->actingAs($this->user)
        ->put('/user/profile-information', ['name' => 'Test', 'email' => 'test@example.com', 'icon_id' => 4151])
        ->assertSessionHasNoErrors();

    expect($this->user->fresh()->icon_id)->toBe(4151);
});

it('keeps the icon when the form leaves it out', function () {
    $this->actingAs($this->user)
        ->put('/user/profile-information', ['name' => 'Test', 'email' => 'test@example.com'])
        ->assertSessionHasNoErrors();

    expect($this->user->fresh()->icon_id)->toBe(995);
});

it('rejects an item that is not in the catalogue, or no item at all', function (mixed $iconId) {
    $this->actingAs($this->user)
        ->put('/user/profile-information', ['name' => 'Test', 'email' => 'test@example.com', 'icon_id' => $iconId])
        ->assertSessionHasErrorsIn('updateProfileInformation', ['icon_id']);

    expect($this->user->fresh()->icon_id)->toBe(995);
})->with([123456, null, 'whip']);
