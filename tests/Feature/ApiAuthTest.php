<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

function makeUser(string $email = 'plugin@test.local', string $password = 'correct-horse'): User
{
    return User::query()->forceCreate([
        'name' => 'Plugin User',
        'email' => $email,
        'password' => Hash::make($password),
        'icon_id' => 0,
    ]);
}

it('issues a Bearer token on valid credentials', function () {
    makeUser();

    $this->postJson('/api/login', [
        'email' => 'plugin@test.local',
        'password' => 'correct-horse',
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['token_type', 'access_token'])
        ->assertJsonPath('token_type', 'Bearer');
});

it('rejects wrong password with 401', function () {
    makeUser();

    $this->postJson('/api/login', [
        'email' => 'plugin@test.local',
        'password' => 'wrong',
    ])->assertUnauthorized();
});

it('rejects unknown email with 401', function () {
    $this->postJson('/api/login', [
        'email' => 'nobody@example.com',
        'password' => 'whatever',
    ])->assertUnauthorized();
});

it('validates email + password are required', function () {
    $this->postJson('/api/login', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it('/api/me returns the authenticated user when the token is valid', function () {
    $user = makeUser();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('email', 'plugin@test.local');
});

it('/api/me is 401 without a token', function () {
    $this->getJson('/api/me')->assertUnauthorized();
});

it('/api/logout deletes the personal access token row used for the call', function () {
    $user = makeUser();
    $token = $user->createToken('test')->plainTextToken;

    expect($user->tokens()->count())->toBe(1);

    $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/logout')
        ->assertSuccessful();

    expect($user->fresh()->tokens()->count())->toBe(0);
    expect(PersonalAccessToken::findToken($token))->toBeNull();
});
