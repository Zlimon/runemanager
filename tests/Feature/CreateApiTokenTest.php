<?php

use App\Models\User;
use Laravel\Jetstream\Features;

test('api tokens can be created', function () {
    if (! Features::hasApiFeatures()) {
        $this->markTestSkipped('API support is not enabled.');
    }

    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $this->post('/user/api-tokens', [
        'name' => 'Test Token',
        'permissions' => [
            'read',
            'update',
        ],
    ]);

    $this->assertCount(1, $user->fresh()->tokens);
    $this->assertEquals('Test Token', $user->fresh()->tokens->first()->name);
    $this->assertTrue($user->fresh()->tokens->first()->can('read'));
    $this->assertFalse($user->fresh()->tokens->first()->can('delete'));
});
