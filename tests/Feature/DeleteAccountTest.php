<?php

use App\Models\Account;
use App\Models\NewsPost;
use App\Models\User;
use Laravel\Jetstream\Features;

test('user accounts can be deleted', function () {
    if (! Features::hasAccountDeletionFeatures()) {
        $this->markTestSkipped('Account deletion is not enabled.');
    }

    $this->actingAs($user = User::factory()->create());

    $this->delete('/user', [
        'password' => 'password',
    ]);

    $this->assertNull($user->fresh());
});

test('a claimed osrs account survives its owner and returns to the roster', function () {
    if (! Features::hasAccountDeletionFeatures()) {
        $this->markTestSkipped('Account deletion is not enabled.');
    }

    $this->actingAs($user = User::factory()->create());
    $account = Account::factory()->for($user)->create(['username' => 'Claimed']);

    $this->delete('/user', [
        'password' => 'password',
    ])->assertSessionHasNoErrors();

    $this->assertNull($user->fresh());
    $this->assertNotNull($account->fresh());
    $this->assertNull($account->fresh()->user_id);
});

test('a news post survives its author', function () {
    if (! Features::hasAccountDeletionFeatures()) {
        $this->markTestSkipped('Account deletion is not enabled.');
    }

    $this->actingAs($user = User::factory()->create());
    $post = NewsPost::factory()->create(['user_id' => $user->id]);

    $this->delete('/user', [
        'password' => 'password',
    ])->assertSessionHasNoErrors();

    $this->assertNull($user->fresh());
    $this->assertNotNull($post->fresh());
    $this->assertNull($post->fresh()->user_id);
});

test('correct password must be provided before account can be deleted', function () {
    if (! Features::hasAccountDeletionFeatures()) {
        $this->markTestSkipped('Account deletion is not enabled.');
    }

    $this->actingAs($user = User::factory()->create());

    $this->delete('/user', [
        'password' => 'wrong-password',
    ]);

    $this->assertNotNull($user->fresh());
});
