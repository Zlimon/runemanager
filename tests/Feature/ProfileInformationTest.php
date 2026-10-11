<?php

use App\Models\User;

test('profile information can be updated', function () {
    $this->actingAs($user = User::factory()->create());

    $this->put('/user/profile-information', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
        'icon_id' => $user->icon_id,
    ]);

    $this->assertEquals('Test Name', $user->fresh()->name);
    $this->assertEquals('test@example.com', $user->fresh()->email);
});
