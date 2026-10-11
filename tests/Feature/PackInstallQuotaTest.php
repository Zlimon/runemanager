<?php

use App\Models\ResourcePack;
use App\Models\User;
use App\Services\ResourcePacks\PackInstallQuota;
use App\Support\Roles;

function quotaPack(string $name, ?User $installedBy = null): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => $name,
        'alias' => ucfirst($name),
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => false,
        'installed_by_user_id' => $installedBy?->id,
    ]);
}

it('reads the limit from config', function () {
    config(['runemanager.resource_packs.user_install_limit' => 3]);

    expect(app(PackInstallQuota::class)->limit())->toBe(3);
});

it('counts only the packs the member installed', function () {
    $member = User::factory()->create();
    $other = User::factory()->create();
    quotaPack('pack-a', $member);
    quotaPack('pack-b', $member);
    quotaPack('pack-c', $other);
    quotaPack('pack-instance');

    expect(app(PackInstallQuota::class)->countFor($member))->toBe(2);
});

it('allows a member below the limit and blocks one at it', function () {
    config(['runemanager.resource_packs.user_install_limit' => 2]);
    $member = User::factory()->create();
    $quota = app(PackInstallQuota::class);

    quotaPack('pack-a', $member);
    expect($quota->allows($member))->toBeTrue();

    quotaPack('pack-b', $member);
    expect($quota->allows($member))->toBeFalse();
});

it('lets a manage-instance holder past the limit', function () {
    config(['runemanager.resource_packs.user_install_limit' => 1]);
    Roles::sync();
    $manager = tap(User::factory()->create())->givePermissionTo(Roles::MANAGE_INSTANCE);
    quotaPack('pack-a', $manager);
    quotaPack('pack-b', $manager);

    expect(app(PackInstallQuota::class)->allows($manager))->toBeTrue();
});

it('lets the owner past the limit', function () {
    config(['runemanager.resource_packs.user_install_limit' => 1]);
    $owner = adminUser();
    quotaPack('pack-a', $owner);

    expect(app(PackInstallQuota::class)->allows($owner))->toBeTrue();
});
