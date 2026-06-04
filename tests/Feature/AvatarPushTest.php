<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

function avatarUser(string $email = 'avatar@test.local'): User
{
    return User::query()->forceCreate([
        'name' => 'Avatar User',
        'email' => $email,
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function avatarHeaders(string $hash = 'hash-avatar', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

it('stores the obj + mtl pair and stamps the account', function () {
    $user = avatarUser();
    Sanctum::actingAs($user);

    $response = $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('player-808-0.obj', "o player\nv 0 0 0\n"),
        'material' => UploadedFile::fake()->createWithContent('player-808-0.mtl', "newmtl c0\nKd 1 0 0\n"),
    ], avatarHeaders())->assertSuccessful();

    $account = Account::firstOrFail();

    Storage::disk('public')->assertExists("avatars/{$account->id}/avatar.obj");
    Storage::disk('public')->assertExists("avatars/{$account->id}/avatar.mtl");

    expect($account->avatar_uploaded_at)->not->toBeNull();
    $response->assertJsonPath('data.avatar_uploaded_at', $account->avatar_uploaded_at->toIso8601String());
});

it('accepts a model with no material and clears any stale material', function () {
    $user = avatarUser();
    Sanctum::actingAs($user);

    // First push includes a material.
    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('a.obj', "v 0 0 0\n"),
        'material' => UploadedFile::fake()->createWithContent('a.mtl', "newmtl c0\n"),
    ], avatarHeaders())->assertSuccessful();

    $account = Account::firstOrFail();
    Storage::disk('public')->assertExists("avatars/{$account->id}/avatar.mtl");

    // Second push drops the material — the stale one must be removed.
    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('b.obj', "v 1 1 1\n"),
    ], avatarHeaders())->assertSuccessful();

    Storage::disk('public')->assertExists("avatars/{$account->id}/avatar.obj");
    Storage::disk('public')->assertMissing("avatars/{$account->id}/avatar.mtl");
});

it('rejects a model that is not an obj file', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('avatar.txt', 'nope'),
    ], avatarHeaders())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['model']);
});

it('rejects a material that is not an mtl file', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('avatar.obj', "v 0 0 0\n"),
        'material' => UploadedFile::fake()->createWithContent('avatar.png', 'nope'),
    ], avatarHeaders())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['material']);
});

it('rejects an upload with no model file', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [], avatarHeaders())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['model']);
});

it('requires authentication', function () {
    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('avatar.obj', "v 0 0 0\n"),
    ], ['Accept' => 'application/json'])->assertUnauthorized();
});

it('requires the account headers', function () {
    Sanctum::actingAs(avatarUser());

    $this->post('/api/plugin/avatar', [
        'model' => UploadedFile::fake()->createWithContent('avatar.obj', "v 0 0 0\n"),
    ], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['X-Account-Hash', 'X-Account-Username']);
});
