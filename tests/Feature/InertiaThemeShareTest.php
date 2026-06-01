<?php

use App\Helpers\SettingHelper;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\ResourcePack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function freshThemeUser(): User
{
    return User::query()->forceCreate([
        'name' => 'Theme User',
        'email' => 'theme@test.local',
        'email_verified_at' => now(),
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function makeThemedPack(string $name, ?string $daisyTheme): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => $name,
        'alias' => ucfirst($name),
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => false,
        'daisyui_theme' => $daisyTheme,
    ]);
}

function shareFor(?User $user): array
{
    $request = Request::create('/dashboard', 'GET');
    if ($user) {
        $request->setUserResolver(fn () => $user);
    }

    return app(HandleInertiaRequests::class)->share($request);
}

it('shares the user override pack theme', function () {
    $user = freshThemeUser();
    $userPack = makeThemedPack('user-theme', 'forest');
    $user->forceFill(['resource_pack_id' => $userPack->id])->save();

    expect(shareFor($user->fresh()))->toMatchArray(['theme' => 'forest']);
});

it('falls back to the global pack theme when the user has no override', function () {
    $user = freshThemeUser();
    $globalPack = makeThemedPack('global-theme', 'dracula');
    SettingHelper::setSetting('resource_pack_id', $globalPack->id, 'int');

    expect(shareFor($user))->toMatchArray(['theme' => 'dracula']);
});

it('falls back to runemanager when neither pack has a daisyui_theme', function () {
    $user = freshThemeUser();
    $pack = makeThemedPack('no-theme', null);
    SettingHelper::setSetting('resource_pack_id', $pack->id, 'int');

    expect(shareFor($user))->toMatchArray(['theme' => 'runemanager']);
});

it('falls back to runemanager when no pack is set at all', function () {
    expect(shareFor(null))->toMatchArray(['theme' => 'runemanager']);
});

it('exposes dark_mode from the resolved pack', function () {
    $user = freshThemeUser();
    ResourcePack::query()->forceCreate([
        'name' => 'dark-pack', 'alias' => 'Dark', 'version' => '1.0', 'author' => 't',
        'url' => 'x', 'tags' => '', 'dark_mode' => true, 'daisyui_theme' => 'dark',
    ]);
    $dark = ResourcePack::where('name', 'dark-pack')->first();
    $user->forceFill(['resource_pack_id' => $dark->id])->save();

    $shared = shareFor($user->fresh());
    expect($shared)->toMatchArray(['theme' => 'dark', 'dark_mode' => true]);
});
