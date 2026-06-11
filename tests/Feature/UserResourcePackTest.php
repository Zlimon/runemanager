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

it('effectiveFor() floors to Default Vanilla when nothing is selected', function () {
    $vanilla = makePack('sample-vanilla');

    expect(ResourcePack::effectiveFor(null)?->id)->toBe($vanilla->id);
});

it('effectiveFor() prefers the user pack, then the global default, over vanilla', function () {
    makePack('sample-vanilla');
    $global = makePack('global-pack');
    $userPack = makePack('user-pack');
    SettingHelper::setSetting('resource_pack_id', $global->id, 'int');

    $guest = freshPackUser('guest@test.local');
    $picky = freshPackUser('picky@test.local');
    $picky->forceFill(['resource_pack_id' => $userPack->id])->save();

    // Guest with no personal pick rides the global default.
    expect(ResourcePack::effectiveFor($guest->fresh())?->id)->toBe($global->id);
    // A personal pick wins.
    expect(ResourcePack::effectiveFor($picky->fresh())?->id)->toBe($userPack->id);
});

it('effectiveFor() returns null only when even vanilla is absent', function () {
    expect(ResourcePack::effectiveFor(null))->toBeNull();
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
        ->postJson(route('user.resource-pack.install'), ['name' => 'pack-sakurascape'])
        ->assertSuccessful()
        ->assertJsonPath('installed', false)
        ->assertJsonPath('queued', true);

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
        ->postJson(route('user.resource-pack.install'), ['name' => 'pack-sakurascape'])
        ->assertSuccessful()
        ->assertJsonPath('installed', true)
        ->assertJsonPath('queued', false);

    expect($user->fresh()->resource_pack_id)->toBe($pack->id);
    Queue::assertNothingPushed();
});

it('website install: rejects names without the pack- prefix', function () {
    $this->actingAs(freshPackUser())
        ->postJson(route('user.resource-pack.install'), ['name' => 'sakurascape'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');

    Queue::assertNothingPushed();
});

it('website install: rejects guests', function () {
    $this->postJson(route('user.resource-pack.install'), ['name' => 'pack-sakurascape'])
        ->assertUnauthorized();

    Queue::assertNothingPushed();
});

it('website install: attributes a newly installed pack to the member', function () {
    $user = freshPackUser();
    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(false));

    $this->actingAs($user)
        ->postJson(route('user.resource-pack.install'), ['name' => 'pack-sakurascape'])
        ->assertSuccessful();

    expect(ResourcePack::firstWhere('name', 'pack-sakurascape')->installed_by_user_id)->toBe($user->id);
});

it('website install: blocks a member who is at their install limit', function () {
    config(['runemanager.resource_packs.user_install_limit' => 2]);
    $user = freshPackUser();
    makePack('pack-a', ['installed_by_user_id' => $user->id]);
    makePack('pack-b', ['installed_by_user_id' => $user->id]);

    $this->actingAs($user)
        ->postJson(route('user.resource-pack.install'), ['name' => 'pack-c'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');

    Queue::assertNothingPushed();
    expect(ResourcePack::where('name', 'pack-c')->exists())->toBeFalse();
});

it('website install: re-adding a pack already in the pool ignores the limit', function () {
    config(['runemanager.resource_packs.user_install_limit' => 1]);
    $user = freshPackUser();
    makePack('pack-a', ['installed_by_user_id' => $user->id]);
    // Installed by someone else, already in the shared pool.
    $shared = makePack('pack-shared', ['version' => '1.0.0', 'background_color' => '#111111']);
    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(true));

    $this->actingAs($user)
        ->postJson(route('user.resource-pack.install'), ['name' => 'pack-shared'])
        ->assertSuccessful();

    expect($user->fresh()->resource_pack_id)->toBe($shared->id);
});

it('website install: the owner is exempt from the install limit', function () {
    config(['runemanager.resource_packs.user_install_limit' => 1]);
    $owner = adminUser();
    makePack('pack-a', ['installed_by_user_id' => $owner->id]);
    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(false));

    $this->actingAs($owner)
        ->postJson(route('user.resource-pack.install'), ['name' => 'pack-b'])
        ->assertSuccessful();

    expect(ResourcePack::where('name', 'pack-b')->exists())->toBeTrue();
});

it('delete: a member can delete a pack they installed and users on it fall back', function () {
    $user = freshPackUser();
    $other = freshPackUser('other@test.local');
    $pack = makePack('pack-mine', ['installed_by_user_id' => $user->id]);
    $user->forceFill(['resource_pack_id' => $pack->id])->save();
    $other->forceFill(['resource_pack_id' => $pack->id])->save();

    $this->actingAs($user)
        ->delete(route('user.resource-pack.destroy', $pack->id))
        ->assertRedirect();

    expect(ResourcePack::find($pack->id))->toBeNull();
    expect($user->fresh()->resource_pack_id)->toBeNull();
    expect($other->fresh()->resource_pack_id)->toBeNull();
});

it('delete: a member cannot delete a pack they did not install', function () {
    $user = freshPackUser();
    $pack = makePack('pack-theirs', ['installed_by_user_id' => freshPackUser('owner@test.local')->id]);

    $this->actingAs($user)
        ->delete(route('user.resource-pack.destroy', $pack->id))
        ->assertForbidden();

    expect(ResourcePack::find($pack->id))->not->toBeNull();
});

it('delete: nobody can delete the bundled vanilla pack', function () {
    $user = freshPackUser();
    $pack = makePack('sample-vanilla', ['installed_by_user_id' => $user->id]);

    $this->actingAs($user)
        ->delete(route('user.resource-pack.destroy', $pack->id))
        ->assertForbidden();
});

it('delete: a member cannot delete the current instance default', function () {
    $user = freshPackUser();
    $pack = makePack('pack-default', ['installed_by_user_id' => $user->id]);
    SettingHelper::setSetting('resource_pack_id', $pack->id, 'int');

    $this->actingAs($user)
        ->delete(route('user.resource-pack.destroy', $pack->id))
        ->assertStatus(422);

    expect(ResourcePack::find($pack->id))->not->toBeNull();
});

it('install status: reports not-installed for a pending stub even with assets on disk', function () {
    makePack('pack-sakurascape', ['version' => 'pending']);
    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(true));

    $this->actingAs(freshPackUser())
        ->getJson(route('user.resource-pack.status', ['name' => 'pack-sakurascape']))
        ->assertSuccessful()
        ->assertJsonPath('installed', false);
});

it('install status: reports installed once the row is populated and assets exist', function () {
    makePack('pack-sakurascape', ['version' => '1.0.0']);
    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(true));

    $this->actingAs(freshPackUser())
        ->getJson(route('user.resource-pack.status', ['name' => 'pack-sakurascape']))
        ->assertSuccessful()
        ->assertJsonPath('installed', true);
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
