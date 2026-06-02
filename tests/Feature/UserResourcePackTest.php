<?php

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function freshPackUser(string $email = 'pack@test.local'): User
{
    return User::query()->forceCreate([
        'name' => 'Pack Tester',
        'email' => $email,
        'email_verified_at' => now(),
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function makePack(string $name): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => $name,
        'alias' => ucfirst($name),
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => false,
    ]);
}

it('rejects unauthenticated requests', function () {
    $this->putJson('/user/resource-pack', ['resource_pack_id' => 1])->assertUnauthorized();
});

it('sets the per-user override on update', function () {
    $user = freshPackUser();
    $pack = makePack('sample-vanilla');

    $this->actingAs($user)
        ->putJson('/user/resource-pack', ['resource_pack_id' => $pack->id])
        ->assertSuccessful()
        ->assertJsonPath('resource_pack_id', $pack->id)
        ->assertJsonPath('effective_resource_pack_id', $pack->id);

    expect($user->fresh()->resource_pack_id)->toBe($pack->id);
});

it('clears the override when sent null', function () {
    $user = freshPackUser();
    $pack = makePack('sample-vanilla');
    $user->forceFill(['resource_pack_id' => $pack->id])->save();

    $this->actingAs($user)
        ->putJson('/user/resource-pack', ['resource_pack_id' => null])
        ->assertSuccessful()
        ->assertJsonPath('resource_pack_id', null);

    expect($user->fresh()->resource_pack_id)->toBeNull();
});

it('rejects unknown pack ids', function () {
    $user = freshPackUser();

    $this->actingAs($user)
        ->putJson('/user/resource-pack', ['resource_pack_id' => 99999])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['resource_pack_id']);
});

it('effectiveResourcePackId() returns the user override when set', function () {
    $user = freshPackUser();
    $userPack = makePack('user-pack');
    $globalPack = makePack('global-pack');

    SettingHelper::setSetting('resource_pack_id', $globalPack->id, 'int');
    $user->forceFill(['resource_pack_id' => $userPack->id])->save();

    expect($user->fresh()->effectiveResourcePackId())->toBe($userPack->id);
});

it('effectiveResourcePackId() falls back to the global setting when user has none', function () {
    $user = freshPackUser();
    $globalPack = makePack('global-pack');

    SettingHelper::setSetting('resource_pack_id', $globalPack->id, 'int');

    expect($user->fresh()->effectiveResourcePackId())->toBe($globalPack->id);
});

it('effectiveResourcePackId() returns null when neither is set', function () {
    $user = freshPackUser();

    expect($user->fresh()->effectiveResourcePackId())->toBeNull();
});

it('does not touch the global setting when a user updates their override', function () {
    $user = freshPackUser();
    $userPack = makePack('user-pack');
    $globalPack = makePack('global-pack');

    SettingHelper::setSetting('resource_pack_id', $globalPack->id, 'int');

    $this->actingAs($user)
        ->putJson('/user/resource-pack', ['resource_pack_id' => $userPack->id])
        ->assertSuccessful();

    expect(SettingHelper::getSetting('resource_pack_id'))->toEqual($globalPack->id);
});

it('plugin push: sets the user override by pack name', function () {
    $user = freshPackUser();
    $pack = makePack('sample-vanilla');

    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/resource-pack', ['name' => 'sample-vanilla'])
        ->assertSuccessful()
        ->assertJsonPath('resource_pack_id', $pack->id)
        ->assertJsonPath('effective_resource_pack_id', $pack->id);

    expect($user->fresh()->resource_pack_id)->toBe($pack->id);
});

it('plugin push: returns 404 on unknown pack name', function () {
    $user = freshPackUser();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/resource-pack', ['name' => 'pack-that-does-not-exist'])
        ->assertNotFound();

    expect($user->fresh()->resource_pack_id)->toBeNull();
});

it('plugin push: rejects unauthenticated requests', function () {
    $this->putJson('/api/plugin/resource-pack', ['name' => 'anything'])->assertUnauthorized();
});

it('plugin push: rejects missing name field', function () {
    Sanctum::actingAs(freshPackUser());

    $this->putJson('/api/plugin/resource-pack', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('plugin push: does not touch the global setting', function () {
    $user = freshPackUser();
    $userPack = makePack('user-pack');
    $globalPack = makePack('global-pack');
    SettingHelper::setSetting('resource_pack_id', $globalPack->id, 'int');

    Sanctum::actingAs($user);
    $this->putJson('/api/plugin/resource-pack', ['name' => 'user-pack'])->assertSuccessful();

    expect(SettingHelper::getSetting('resource_pack_id'))->toEqual($globalPack->id);
});
