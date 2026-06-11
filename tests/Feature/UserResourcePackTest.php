<?php

use App\Helpers\SettingHelper;
use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Queue::fake();
});

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

function makePack(string $name, array $overrides = []): ResourcePack
{
    return ResourcePack::query()->forceCreate(array_merge([
        'name' => $name,
        'alias' => ucfirst($name),
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => false,
    ], $overrides));
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

it('website install: stub-creates the row, selects it and queues a fetch when not installed', function () {
    $user = freshPackUser();

    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(false));

    expect(ResourcePack::count())->toBe(0);

    $this->actingAs($user)
        ->post(route('user.resource-pack.install'), ['name' => 'pack-sakurascape'])
        ->assertRedirect();

    $pack = ResourcePack::firstWhere('name', 'pack-sakurascape');
    expect($pack)->not->toBeNull();
    expect($user->fresh()->resource_pack_id)->toBe($pack->id);

    Queue::assertPushed(FetchResourcePackJob::class, fn ($job) => $job->packName === 'pack-sakurascape');
});

it('website install: selects without re-queuing when the pack is already installed', function () {
    $user = freshPackUser();
    $pack = makePack('pack-sakurascape', [
        'version' => '1.0.0',
        'background_color' => '#483f33',
        'accent_color' => '#88827a',
    ]);

    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(true));

    $this->actingAs($user)
        ->post(route('user.resource-pack.install'), ['name' => 'pack-sakurascape'])
        ->assertRedirect();

    expect($user->fresh()->resource_pack_id)->toBe($pack->id);
    Queue::assertNothingPushed();
});

it('website install: rejects names without the pack- prefix', function () {
    $this->actingAs(freshPackUser())
        ->post(route('user.resource-pack.install'), ['name' => 'sakurascape'])
        ->assertSessionHasErrors('name');

    Queue::assertNothingPushed();
});

it('website install: rejects guests', function () {
    $this->post(route('user.resource-pack.install'), ['name' => 'pack-sakurascape'])
        ->assertRedirect(route('login'));

    Queue::assertNothingPushed();
});

it('plugin push: 200 + no job queued when pack is already installed', function () {
    $user = freshPackUser();
    $pack = makePack('sample-vanilla');

    // Pretend the assets are on disk.
    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(true));

    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/resource-pack', ['name' => 'sample-vanilla'])
        ->assertStatus(200)
        ->assertJsonPath('resource_pack_id', $pack->id)
        ->assertJsonPath('installed', true)
        ->assertJsonPath('queued', false);

    Queue::assertNothingPushed();
    expect($user->fresh()->resource_pack_id)->toBe($pack->id);
});

it('plugin push: 202 + queues install when DB row exists but assets are missing', function () {
    $user = freshPackUser();
    $pack = makePack('sample-vanilla');

    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(false));

    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/resource-pack', ['name' => 'sample-vanilla'])
        ->assertStatus(202)
        ->assertJsonPath('queued', true);

    Queue::assertPushed(FetchResourcePackJob::class, fn ($job) => $job->packName === 'sample-vanilla');
    expect($user->fresh()->resource_pack_id)->toBe($pack->id);
});

it('plugin push: 202 + queues install when DB row is a pending stub (even with assets on disk)', function () {
    // A previously installed pack survived a migrate:fresh / row purge — the
    // CSS file sits on disk but the new row carries version=pending and
    // background_color=null. Hitting the endpoint should re-run the install
    // pipeline so the row gets metadata and the palette gets extracted.
    $user = freshPackUser();
    $stub = makePack('sample-vanilla', [
        'version' => 'pending',
        'background_color' => null,
        'accent_color' => null,
    ]);

    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(true));

    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/resource-pack', ['name' => 'sample-vanilla'])
        ->assertStatus(202)
        ->assertJsonPath('queued', true)
        ->assertJsonPath('installed', false);

    Queue::assertPushed(FetchResourcePackJob::class, fn ($job) => $job->packName === 'sample-vanilla');
    expect($user->fresh()->resource_pack_id)->toBe($stub->id);
});

it('plugin push: 200 + does not re-queue when pack row is fully populated', function () {
    // Real installed pack — version is set + colours extracted — should NOT
    // re-fetch on subsequent pushes.
    $user = freshPackUser();
    $pack = makePack('sample-vanilla', [
        'version' => '1.0.0',
        'background_color' => '#483f33',
        'accent_color' => '#88827a',
    ]);

    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(true));

    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/resource-pack', ['name' => 'sample-vanilla'])
        ->assertStatus(200)
        ->assertJsonPath('queued', false)
        ->assertJsonPath('installed', true);

    Queue::assertNothingPushed();
    expect($user->fresh()->resource_pack_id)->toBe($pack->id);
});

it('plugin push: 202 + stub-creates row + queues install for an unknown pack', function () {
    $user = freshPackUser();

    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(false));

    Sanctum::actingAs($user);

    expect(ResourcePack::count())->toBe(0);

    $this->putJson('/api/plugin/resource-pack', ['name' => 'brand-new-pack'])
        ->assertStatus(202)
        ->assertJsonPath('queued', true);

    expect(ResourcePack::count())->toBe(1);
    $stub = ResourcePack::first();
    expect($stub->name)->toBe('brand-new-pack');
    expect($stub->version)->toBe('pending');
    expect($user->fresh()->resource_pack_id)->toBe($stub->id);

    Queue::assertPushed(FetchResourcePackJob::class, fn ($job) => $job->packName === 'brand-new-pack');
});

it('plugin push: rejects names with disallowed characters', function () {
    Sanctum::actingAs(freshPackUser());

    $this->putJson('/api/plugin/resource-pack', ['name' => '../etc/passwd'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    Queue::assertNothingPushed();
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
