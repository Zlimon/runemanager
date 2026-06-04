<?php

use App\Models\Account;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function announcementPluginHeaders(string $hash = 'hash-ann', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

it('returns active, unacknowledged announcements as a bare array', function () {
    $author = User::factory()->withPersonalTeam()->create();
    Announcement::factory()->for($author)->create(['title' => 'Live']);
    Announcement::factory()->expired()->for($author)->create(['title' => 'Expired']);

    Sanctum::actingAs(User::factory()->withPersonalTeam()->create());

    $this->getJson('/api/plugin/announcements', announcementPluginHeaders())
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.title', 'Live');
});

it('stops returning an announcement once acknowledged', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $announcement = Announcement::factory()->for($author)->create();

    Sanctum::actingAs(User::factory()->withPersonalTeam()->create());

    $this->getJson('/api/plugin/announcements', announcementPluginHeaders())->assertJsonCount(1);

    $this->putJson("/api/plugin/announcements/{$announcement->id}/acknowledge", [], announcementPluginHeaders())
        ->assertOk();

    $this->getJson('/api/plugin/announcements', announcementPluginHeaders())->assertJsonCount(0);

    $account = Account::firstOrFail();
    expect($announcement->acknowledgedBy()->whereKey($account->id)->exists())->toBeTrue();
});

it('rejects unauthenticated requests', function () {
    $this->getJson('/api/plugin/announcements')->assertUnauthorized();
});
