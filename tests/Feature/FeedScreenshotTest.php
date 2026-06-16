<?php

use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function screenshotAccount(): Account
{
    $user = User::factory()->withPersonalTeam()->create();

    return Account::factory()->for($user)->create([
        'username' => 'Shotter',
        'account_hash' => 'shot-hash',
    ]);
}

function screenshotHeaders(): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'shot-hash',
        'X-Account-Username' => 'Shotter',
    ];
}

it('attaches a screenshot to the most recent matching feed event', function () {
    Storage::fake('public');
    $account = screenshotAccount();
    Sanctum::actingAs($account->user);

    $event = FeedEvent::factory()->for($account)->lootDrop()->create();

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'loot_drop']), [
        'image' => UploadedFile::fake()->image('shot.png', 400, 300),
    ], screenshotHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.attached', true);

    $event->refresh();
    expect($event->screenshot_path)->not->toBeNull();
    Storage::disk('public')->assertExists($event->screenshot_path);
});

it('discards the screenshot when no matching event exists', function () {
    Storage::fake('public');
    $account = screenshotAccount();
    Sanctum::actingAs($account->user);

    // A combat-achievement event exists, but the screenshot is for a loot drop.
    FeedEvent::factory()->for($account)->combatAchievement()->create();

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'loot_drop']), [
        'image' => UploadedFile::fake()->image('shot.png'),
    ], screenshotHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.attached', false);
});

it('does not attach to an event that already has a screenshot', function () {
    Storage::fake('public');
    $account = screenshotAccount();
    Sanctum::actingAs($account->user);

    $event = FeedEvent::factory()->for($account)->lootDrop()->create(['screenshot_path' => 'feed-screenshots/old.png']);

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'loot_drop']), [
        'image' => UploadedFile::fake()->image('shot.png'),
    ], screenshotHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.attached', false);

    expect($event->refresh()->screenshot_path)->toBe('feed-screenshots/old.png');
});

it('rejects a non-image upload and an unknown type', function () {
    Storage::fake('public');
    $account = screenshotAccount();
    Sanctum::actingAs($account->user);

    $this->postJson(route('api.plugin.feed.screenshot', ['type' => 'loot_drop']), [], screenshotHeaders())
        ->assertStatus(422)->assertJsonValidationErrors('image');

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'level_up']), [
        'image' => UploadedFile::fake()->image('shot.png'),
    ], screenshotHeaders())->assertStatus(422)->assertJsonValidationErrors('type');
});

it('exposes screenshot_url on the feed resource', function () {
    Storage::fake('public');
    $account = screenshotAccount();
    FeedEvent::factory()->for($account)->lootDrop()->create(['screenshot_path' => 'feed-screenshots/5.png']);

    $this->get(route('feed.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('events.0.screenshot_url', fn ($url) => str_contains((string) $url, 'feed-screenshots/5.png')));
});
