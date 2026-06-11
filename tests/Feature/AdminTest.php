<?php

use App\Helpers\SettingHelper;
use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\ResourcePacks\ResourcePackHub;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

function adminPack(string $name = 'pack-deletable'): ResourcePack
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

it('forbids guests from the admin dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('forbids members from the admin dashboard in casual mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);

    $this->actingAs(adminUser(Roles::USER))
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('lets admins view the dashboard', function () {
    $this->actingAs(adminUser())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->where('mode', Instance::MODE_CASUAL),
        );
});

it('does not elevate a plain user in group mode (clan/group elevation deferred)', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP);

    $this->actingAs(adminUser(Roles::USER))
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('lets the owner reach the dashboard', function () {
    $this->actingAs(adminUser(Roles::OWNER))
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('lets admins update instance settings', function () {
    $this->actingAs(adminUser())
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CLAN,
            'clan_name' => 'Knights of Falador',
            'group_name' => '',
            'resource_pack_id' => null,
        ])
        ->assertRedirect();

    expect(Instance::mode())->toBe(Instance::MODE_CLAN);
    expect(Instance::name())->toBe('Knights of Falador');
});

it('validates the instance mode', function () {
    $this->actingAs(adminUser())
        ->putJson(route('admin.settings.update'), [
            'instance_mode' => 'not-a-mode',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('instance_mode');
});

it('shares the admin flag with the frontend for admins', function () {
    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('is_admin', true));
});

it('does not flag members as admin in casual mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);

    $this->actingAs(adminUser(Roles::USER))
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('is_admin', false));
});

it('lets the owner delete any pack, clearing the default and resetting users', function () {
    $owner = adminUser();
    $member = User::factory()->create(['resource_pack_id' => null]);
    $pack = adminPack();
    $member->forceFill(['resource_pack_id' => $pack->id])->save();
    SettingHelper::setSetting('resource_pack_id', $pack->id, 'int');

    $this->actingAs($owner)
        ->delete(route('admin.packs.destroy', $pack->id))
        ->assertRedirect();

    expect(ResourcePack::find($pack->id))->toBeNull();
    expect($member->fresh()->resource_pack_id)->toBeNull();
    expect((int) SettingHelper::getSetting('resource_pack_id', 0))->toBe(0);
});

it('forbids the owner from deleting the bundled vanilla pack', function () {
    $pack = adminPack('sample-vanilla');

    $this->actingAs(adminUser())
        ->delete(route('admin.packs.destroy', $pack->id))
        ->assertForbidden();

    expect(ResourcePack::find($pack->id))->not->toBeNull();
});

it('forbids members from deleting packs via the admin route', function () {
    $pack = adminPack();

    $this->actingAs(adminUser(Roles::USER))
        ->delete(route('admin.packs.destroy', $pack->id))
        ->assertForbidden();

    expect(ResourcePack::find($pack->id))->not->toBeNull();
});

it('lists installed packs with installer name and usage count', function () {
    $this->mock(ResourcePackHub::class,
        fn ($m) => $m->shouldReceive('available')->andReturn([]));

    $installer = User::factory()->create(['name' => 'Zezima']);
    $pack = adminPack('pack-rs3');
    $pack->forceFill(['installed_by_user_id' => $installer->id])->save();
    User::factory()->count(2)->create(['resource_pack_id' => $pack->id]);

    $this->actingAs(adminUser())
        ->get(route('admin.packs'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Packs')
            ->has('installed', 1)
            ->where('installed.0.installed_by', 'Zezima')
            ->where('installed.0.users_count', 2)
            ->where('installed.0.is_vanilla', false),
        );
});

it('returns JSON for an ajax hub install so the page can poll', function () {
    Queue::fake();

    $this->actingAs(adminUser())
        ->postJson(route('admin.packs.install'), ['name' => 'pack-win95ish'])
        ->assertSuccessful()
        ->assertJsonPath('queued', true);

    Queue::assertPushed(FetchResourcePackJob::class);
});
