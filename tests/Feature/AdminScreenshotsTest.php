<?php

use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\User;
use App\Services\Screenshots\ScreenshotStorage;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');

    $this->owner = User::factory()->withPersonalTeam()->create();
    $this->account = Account::factory()->for($this->owner)->create(['username' => 'Shotter']);
});

/** A real PNG in screenshot storage, as the plugin would have uploaded it. */
function moderationStoredShot(): string
{
    return (string) app(ScreenshotStorage::class)->put(UploadedFile::fake()->image('shot.png', 8, 8)->getContent(), 'png');
}

/**
 * @param  array<string, mixed>  $attributes
 */
function moderationShotEvent(Account $account, array $attributes = []): FeedEvent
{
    return FeedEvent::factory()->for($account)->lootDrop()->create([
        'screenshot_path' => moderationStoredShot(),
        ...$attributes,
    ]);
}

it('forbids guests from the admin screenshots page', function () {
    $this->get(route('admin.screenshots'))->assertRedirect(route('login'));
});

it('forbids a user without manage instance from the admin screenshots page', function () {
    $this->actingAs(adminUser(Roles::USER))
        ->get(route('admin.screenshots'))
        ->assertForbidden();
});

it('lists every account\'s screenshots, hidden ones included, newest first', function () {
    $second = Account::factory()->for($this->owner)->create(['username' => 'Alt']);
    $newest = moderationShotEvent($this->account, ['occurred_at' => now()->subMinute(), 'screenshot_hidden' => true]);
    moderationShotEvent($second, ['occurred_at' => now()->subMinutes(2)]);

    $this->actingAs(adminUser())
        ->get(route('admin.screenshots'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Screenshots/Index')
            ->has('screenshots.data', 2)
            ->where('screenshots.data.0.id', $newest->id)
            ->where('screenshots.data.0.hidden', true)
            ->where('screenshots.data.0.account.username', 'Shotter')
            ->has('types')
            ->where('filters', ['username' => null, 'type' => null, 'visibility' => null]),
        );
});

it('gives each screenshot its account id and username, for the accounts.show link', function () {
    $event = moderationShotEvent($this->account);

    $this->actingAs(adminUser())
        ->get(route('admin.screenshots'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('screenshots.data.0.account', ['id' => $this->account->id, 'username' => 'Shotter']));
});

it('filters the admin queue by username, type and visibility', function () {
    $second = Account::factory()->for($this->owner)->create(['username' => 'Alt']);
    moderationShotEvent($this->account, ['occurred_at' => now()->subMinute()]);
    moderationShotEvent($second, ['occurred_at' => now()->subMinutes(2), 'screenshot_hidden' => true]);
    FeedEvent::factory()->for($this->account)->pet()->create([
        'screenshot_path' => moderationStoredShot(),
        'occurred_at' => now()->subMinutes(3),
    ]);

    $this->actingAs(adminUser())
        ->get(route('admin.screenshots', ['username' => 'alt']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('screenshots.data', 1)
            ->where('screenshots.data.0.account.username', 'Alt'));

    $this->actingAs(adminUser())
        ->get(route('admin.screenshots', ['type' => 'pet']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('screenshots.data', 1)
            ->where('screenshots.data.0.type', 'pet'));

    $this->actingAs(adminUser())
        ->get(route('admin.screenshots', ['visibility' => 'hidden']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('screenshots.data', 1)
            ->where('screenshots.data.0.hidden', true));
});

it('lets an admin hide and show any account\'s screenshots in bulk', function () {
    $second = Account::factory()->for($this->owner)->create(['username' => 'Alt']);
    $mine = moderationShotEvent($this->account);
    $theirs = moderationShotEvent($second);

    $this->actingAs(adminUser())
        ->from(route('admin.screenshots'))
        ->patch(route('admin.screenshots.update'), ['ids' => [$mine->id, $theirs->id], 'hidden' => true])
        ->assertRedirect(route('admin.screenshots'))
        ->assertSessionHas('flash.banner', 'Hid 2 screenshots.');

    expect($mine->fresh()->screenshot_hidden)->toBeTrue()
        ->and($theirs->fresh()->screenshot_hidden)->toBeTrue();
});

it('lets an admin delete another account\'s screenshot and its file', function () {
    $event = moderationShotEvent($this->account, ['screenshot_hidden' => true]);
    $path = $event->screenshot_path;

    $this->actingAs(adminUser())
        ->delete(route('admin.screenshots.destroy'), ['ids' => [$event->id]])
        ->assertRedirect()
        ->assertSessionHas('flash.banner', 'Deleted 1 screenshot.');

    expect($event->fresh())->not->toBeNull()
        ->and($event->fresh()->screenshot_path)->toBeNull()
        ->and($event->fresh()->screenshot_hidden)->toBeFalse();
    Storage::disk('local')->assertMissing($path);
});

it('forbids a non-admin from the admin bulk routes', function () {
    $event = moderationShotEvent($this->account);

    $this->actingAs(adminUser(Roles::USER))
        ->patch(route('admin.screenshots.update'), ['ids' => [$event->id], 'hidden' => true])
        ->assertForbidden();
    $this->actingAs(adminUser(Roles::USER))
        ->delete(route('admin.screenshots.destroy'), ['ids' => [$event->id]])
        ->assertForbidden();
});

it('validates the admin bulk actions', function () {
    $this->actingAs(adminUser())
        ->patch(route('admin.screenshots.update'), ['ids' => [], 'hidden' => true])
        ->assertSessionHasErrors('ids');
    $this->actingAs(adminUser())
        ->delete(route('admin.screenshots.destroy'), ['ids' => range(1, 101)])
        ->assertSessionHasErrors('ids');
});

it('throttles the admin screenshot writes in their own bucket', function () {
    $event = moderationShotEvent($this->account);
    $admin = adminUser();

    foreach (range(1, 60) as $attempt) {
        $this->actingAs($admin)
            ->patch(route('admin.screenshots.update'), ['ids' => [$event->id], 'hidden' => true])
            ->assertRedirect();
    }

    $this->actingAs($admin)
        ->delete(route('admin.screenshots.destroy'), ['ids' => [$event->id]])
        ->assertTooManyRequests();
});
