<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists the authenticated user dark-mode preference', function () {
    $user = User::factory()->create(['dark_mode' => false]);

    $this->actingAs($user)
        ->put(route('user.dark-mode.update'), ['dark_mode' => true])
        ->assertRedirect();

    expect($user->refresh()->dark_mode)->toBeTrue();
});

it('requires a boolean dark_mode value', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson(route('user.dark-mode.update'), ['dark_mode' => 'maybe'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['dark_mode']);
});

it('rejects guests', function () {
    $this->put(route('user.dark-mode.update'), ['dark_mode' => true])
        ->assertRedirect(route('login'));
});
