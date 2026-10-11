<?php

use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\User;
use App\Services\Screenshots\ScreenshotStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

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
    Storage::fake('local');
    $account = screenshotAccount();
    Sanctum::actingAs($account->user);

    $event = FeedEvent::factory()->for($account)->lootDrop()->create();

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'loot_drop']), [
        'image' => UploadedFile::fake()->image('shot.png', 400, 300),
    ], screenshotHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.attached', true);

    $event->refresh();
    expect($event->screenshot_path)->toMatch('#^feed-screenshots/[A-Za-z0-9]{40}\.png$#')
        ->and($event->screenshot_hidden)->toBeFalse();
    Storage::disk('local')->assertExists($event->screenshot_path);
});

it('stores a new screenshot hidden when the account hides new screenshots', function () {
    Storage::fake('local');
    $account = screenshotAccount();
    $account->forceFill(['hide_new_screenshots' => true])->save();
    Sanctum::actingAs($account->user);

    $event = FeedEvent::factory()->for($account)->lootDrop()->create();

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'loot_drop']), [
        'image' => UploadedFile::fake()->image('shot.png'),
    ], screenshotHeaders())->assertJsonPath('data.attached', true);

    expect($event->refresh()->screenshot_hidden)->toBeTrue();
});

it('leaves the event without a screenshot when the file cannot be stored', function () {
    $account = screenshotAccount();
    Sanctum::actingAs($account->user);
    $this->partialMock(ScreenshotStorage::class, fn ($mock) => $mock->shouldReceive('store')->andReturn(null));

    $event = FeedEvent::factory()->for($account)->lootDrop()->create();

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'loot_drop']), [
        'image' => UploadedFile::fake()->image('shot.png'),
    ], screenshotHeaders())->assertJsonPath('data.attached', false);

    expect($event->refresh()->screenshot_path)->toBeNull();
});

it('never stores a screenshot on the public disk', function () {
    Storage::fake('local');
    Storage::fake('public');
    $account = screenshotAccount();
    Sanctum::actingAs($account->user);

    FeedEvent::factory()->for($account)->lootDrop()->create();

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'loot_drop']), [
        'image' => UploadedFile::fake()->image('shot.png'),
    ], screenshotHeaders())->assertJsonPath('data.attached', true);

    expect(Storage::disk('public')->allFiles())->toBe([]);
});

it('discards the screenshot when no matching event exists', function () {
    Storage::fake('local');
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
    Storage::fake('local');
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

it('matches on the validated body type, not an unvalidated query-string type', function () {
    Storage::fake('local');
    $account = screenshotAccount();
    Sanctum::actingAs($account->user);

    $quest = FeedEvent::factory()->for($account)->questComplete()->create();
    $loot = FeedEvent::factory()->for($account)->lootDrop()->create();

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'quest_complete']), [
        'type' => 'loot_drop',
        'image' => UploadedFile::fake()->image('shot.png'),
    ], screenshotHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.attached', true);

    expect($loot->refresh()->screenshot_path)->not->toBeNull()
        ->and($quest->refresh()->screenshot_path)->toBeNull();
});

it('rejects a non-image upload and an unknown type', function () {
    Storage::fake('local');
    $account = screenshotAccount();
    Sanctum::actingAs($account->user);

    $this->postJson(route('api.plugin.feed.screenshot', ['type' => 'loot_drop']), [], screenshotHeaders())
        ->assertStatus(422)->assertJsonValidationErrors('image');

    $this->post(route('api.plugin.feed.screenshot', ['type' => 'quest_complete']), [
        'image' => UploadedFile::fake()->image('shot.png'),
    ], screenshotHeaders())->assertStatus(422)->assertJsonValidationErrors('type');
});

it('exposes the gated screenshot route, not the file, on the feed resource', function () {
    Storage::fake('local');
    $account = screenshotAccount();
    $event = FeedEvent::factory()->for($account)->lootDrop()->create(['screenshot_path' => 'feed-screenshots/abc.png']);

    $this->get(route('feed.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('events.0.screenshot_url', route('screenshots.file', $event->id)));
});
