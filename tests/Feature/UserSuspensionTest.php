<?php

use App\Http\Middleware\EnsureUserIsNotSuspended;
use App\Models\User;
use App\Services\Users\CredentialCheck;
use App\Services\Users\UnsuspendUser;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function suspendedUser(): User
{
    Roles::sync();

    return tap(User::factory()->create([
        'email' => 'suspended@test.local',
        'suspended_at' => now(),
        'suspension_reason' => 'Botting',
    ]))->assignRole(Roles::USER);
}

it('refuses a suspended user at the website login', function () {
    suspendedUser();

    $this->post('/login', ['email' => 'suspended@test.local', 'password' => 'password'])
        ->assertSessionHasErrors(['email' => EnsureUserIsNotSuspended::MESSAGE]);

    $this->assertGuest();
});

it('does not reveal a suspension to a wrong password', function () {
    suspendedUser();

    $this->post('/login', ['email' => 'suspended@test.local', 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => trans('auth.failed')]);

    $this->assertGuest();
});

it('refuses a suspended user at the plugin login', function () {
    suspendedUser();

    $this->postJson('/api/login', ['email' => 'suspended@test.local', 'password' => 'password'])
        ->assertForbidden()
        ->assertJson(['message' => EnsureUserIsNotSuspended::MESSAGE]);

    expect(User::first()->tokens()->count())->toBe(0);
});

it('refuses a token that outlived the suspension on /api/me', function () {
    $user = suspendedUser();
    $token = $user->createToken('plugin')->plainTextToken;

    $this->withToken($token)->getJson('/api/me')
        ->assertForbidden()
        ->assertJson(['message' => EnsureUserIsNotSuspended::MESSAGE]);
});

it('refuses a token that outlived the suspension on a plugin push', function () {
    $user = suspendedUser();
    $token = $user->createToken('plugin')->plainTextToken;

    $this->withToken($token)->putJson('/api/plugin/heartbeat', [], [
        'X-Account-Hash' => 'hash-suspended',
        'X-Account-Username' => 'Zlimon',
    ])->assertForbidden()->assertJson(['message' => EnsureUserIsNotSuspended::MESSAGE]);

    $this->assertDatabaseMissing('accounts', ['account_hash' => 'hash-suspended']);
});

it('still accepts an active user token', function () {
    Roles::sync();
    $user = User::factory()->create();
    $token = $user->createToken('plugin')->plainTextToken;

    $this->withToken($token)->getJson('/api/me')->assertOk()->assertJsonPath('id', $user->id);
});

it('signs a suspended user out of an existing web session', function () {
    $user = suspendedUser();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', EnsureUserIsNotSuspended::MESSAGE);

    $this->assertGuest('web');
});

it('lets a user log in again once the suspension is lifted', function () {
    $user = suspendedUser();
    app(UnsuspendUser::class)->run($user);

    $this->post('/login', ['email' => 'suspended@test.local', 'password' => 'password'])
        ->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user->fresh());

    $this->postJson('/api/login', ['email' => 'suspended@test.local', 'password' => 'password'])
        ->assertOk()
        ->assertJsonStructure(['access_token']);
});

/**
 * Make CredentialCheck build and keep its real throwaway hash before a test
 * mocks the Hash facade, so no mocked value outlives the test in its cache.
 */
function warmDummyHash(): void
{
    app(CredentialCheck::class)->user('nobody@test.local', 'whatever');
}

it('runs the password hash check for an unknown email too', function () {
    // Otherwise an unknown email answers faster than a registered one.
    warmDummyHash();
    Hash::shouldReceive('check')->once()->andReturnFalse();

    $this->postJson('/api/login', ['email' => 'nobody@test.local', 'password' => 'whatever'])
        ->assertUnauthorized();
});

it('runs the password hash check for an unknown email at the website login', function () {
    warmDummyHash();
    Hash::shouldReceive('check')->once()->andReturnFalse();

    $this->post('/login', ['email' => 'nobody@test.local', 'password' => 'whatever'])
        ->assertSessionHasErrors('email');
});
