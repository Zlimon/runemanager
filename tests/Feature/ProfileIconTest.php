<?php

use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;
use Laravel\Jetstream\Jetstream;

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
    // The flags and release date are what randomItemId() samples from.
    $tradeable = ['noted' => false, 'placeholder' => false, 'duplicate' => false, 'release_date' => '2005-01-26'];
    $collection->insertOne(['id' => '4151', 'name' => 'Abyssal whip', 'icon' => 'd2hpcA=='] + $tradeable);
    $collection->insertOne(['id' => '995', 'name' => 'Coins', 'icon' => 'Y29pbnM='] + $tradeable);

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

it('rejects an item that is not in the catalogue, or no item at all', function (mixed $iconId) {
    $this->actingAs($this->user)
        ->put('/user/profile-information', ['name' => 'Test', 'email' => 'test@example.com', 'icon_id' => $iconId])
        ->assertSessionHasErrorsIn('updateProfileInformation', ['icon_id']);

    expect($this->user->fresh()->icon_id)->toBe(995);
})->with([123456, null, 'whip']);

it('rejects a profile update without an icon', function () {
    $this->actingAs($this->user)
        ->put('/user/profile-information', ['name' => 'Test', 'email' => 'test@example.com'])
        ->assertSessionHasErrorsIn('updateProfileInformation', ['icon_id']);

    expect($this->user->fresh()->icon_id)->toBe(995);
});

it('starts the register form on a random icon from the catalogue', function () {
    auth()->logout();

    $this->get('/register')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Auth/Register')
            ->where('defaultIcon.id', fn (int $id) => in_array($id, [4151, 995], true))
            ->has('defaultIcon.name')
            ->has('defaultIcon.icon'));
});

it('registers with the icon the user picked, and requires one', function () {
    auth()->logout();
    $payload = [
        'name' => 'New',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
    ];

    $this->post('/register', $payload)->assertSessionHasErrors(['icon_id' => 'Pick an icon.']);
    $this->post('/register', $payload + ['icon_id' => 123456])->assertSessionHasErrors('icon_id');
    expect(User::where('email', 'new@example.com')->exists())->toBeFalse();

    $this->post('/register', $payload + ['icon_id' => 4151])->assertRedirect();

    expect(User::where('email', 'new@example.com')->value('icon_id'))->toBe(4151);
});

it('hands guests a random catalogue item for the random icon button', function () {
    auth()->logout();

    $this->getJson(route('items.random'))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('id', fn (int $id) => in_array($id, [4151, 995], true))
            ->whereType('name', 'string')
            ->whereType('icon', 'string'));

    $this->getJson(route('items.search', ['q' => 'whip']))
        ->assertOk()
        ->assertJsonPath('0.name', 'Abyssal whip');
});
