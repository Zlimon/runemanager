<?php

use App\Models\Team;
use App\Models\User;

test('teams can be deleted', function () {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $user->ownedTeams()->save($team = Team::factory()->make([
        'personal_team' => false,
    ]));

    $team->users()->attach(
        $otherUser = User::factory()->create(), ['role' => 'test-role']
    );

    $this->delete('/teams/'.$team->id);

    $this->assertNull($team->fresh());
    $this->assertCount(0, $otherUser->fresh()->teams);
});

test('personal teams cant be deleted', function () {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $this->delete('/teams/'.$user->currentTeam->id);

    $this->assertNotNull($user->currentTeam->fresh());
});
