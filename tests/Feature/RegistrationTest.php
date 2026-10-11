<?php

use App\Models\User;
use App\Support\Roles;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;

test('registration screen can be rendered', function () {
    if (! Features::enabled(Features::registration())) {
        $this->markTestSkipped('Registration support is not enabled.');
    }

    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('registration screen cannot be rendered if support is disabled', function () {
    if (Features::enabled(Features::registration())) {
        $this->markTestSkipped('Registration support is enabled.');
    }

    $response = $this->get('/register');

    $response->assertStatus(404);
});

test('new users can register', function () {
    if (! Features::enabled(Features::registration())) {
        $this->markTestSkipped('Registration support is not enabled.');
    }

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        'icon_id' => 4151, // Abyssal whip, from the shared item catalogue
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('first user becomes owner and later users become members', function () {
    if (! Features::enabled(Features::registration())) {
        $this->markTestSkipped('Registration support is not enabled.');
    }

    $this->post('/register', [
        'name' => 'First User',
        'email' => 'first@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        'icon_id' => 4151, // Abyssal whip, from the shared item catalogue
    ]);

    $this->post('/logout');

    $this->post('/register', [
        'name' => 'Second User',
        'email' => 'second@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        'icon_id' => 4151, // Abyssal whip, from the shared item catalogue
    ]);

    $first = User::where('email', 'first@example.com')->firstOrFail();
    $second = User::where('email', 'second@example.com')->firstOrFail();

    $this->assertTrue($first->hasRole(Roles::OWNER));
    $this->assertTrue($second->hasRole(Roles::USER));
});
