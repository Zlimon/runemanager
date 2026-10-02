<?php

use App\Events\FeedEventCreated;
use App\Helpers\SettingHelper;
use App\Http\Resources\RecentEventResource;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\User;
use App\Services\Accounts\RecentEvents;
use App\Services\Instance\ResetInstanceData;
use App\Services\Screenshots\MoveScreenshotsToPrivateDisk;
use App\Services\Screenshots\ScreenshotStorage;
use App\Support\Instance;
use App\Support\Roles;
use Database\Seeders\DemoScreenshotsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Laravel\Jetstream\Jetstream;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['inertia.ssr.enabled' => false]);
    Storage::fake('local');
    Storage::fake('public');
    Roles::sync();

    $this->owner = User::factory()->withPersonalTeam()->create();
    $this->account = Account::factory()->for($this->owner)->create(['username' => 'Shotter']);

    $this->member = User::factory()->withPersonalTeam()->create();
    $this->memberAccount = Account::factory()->for($this->member)->create(['username' => 'Other']);

    $this->admin = tap(User::factory()->withPersonalTeam()->create())->givePermissionTo(Roles::MANAGE_INSTANCE);
});

/** A real PNG in screenshot storage, as the plugin would have uploaded it. */
function storedShot(): string
{
    return (string) app(ScreenshotStorage::class)->put(UploadedFile::fake()->image('shot.png', 8, 8)->getContent(), 'png');
}

/**
 * @param  array<string, mixed>  $attributes
 */
function shotEvent(Account $account, array $attributes = []): FeedEvent
{
    return FeedEvent::factory()->for($account)->lootDrop()->create([
        'screenshot_path' => storedShot(),
        ...$attributes,
    ]);
}

// --- Serving -----------------------------------------------------------------

it('serves a visible screenshot to everyone, guests included', function (?string $viewer) {
    $event = shotEvent($this->account);

    if ($viewer !== null) {
        $this->actingAs($this->{$viewer});
    }

    $this->get(route('screenshots.file', $event))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('ETag')
        ->assertHeader('Last-Modified')
        ->assertHeader('Cache-Control', 'max-age=300, private');
})->with([
    'guest' => [null],
    'member' => ['member'],
    'owner' => ['owner'],
    'admin' => ['admin'],
]);

it('serves a hidden screenshot only to the owner and admins, and 404s for anyone else', function (?string $viewer, bool $visible) {
    $event = shotEvent($this->account, ['screenshot_hidden' => true]);

    if ($viewer !== null) {
        $this->actingAs($this->{$viewer});
    }

    $response = $this->get(route('screenshots.file', $event));

    $visible ? $response->assertOk() : $response->assertNotFound();
})->with([
    'guest' => [null, false],
    'member' => ['member', false],
    'owner' => ['owner', true],
    'admin' => ['admin', true],
]);

it('404s for an event without a screenshot, a missing file and an unknown event', function () {
    $without = FeedEvent::factory()->for($this->account)->lootDrop()->create();
    $missing = FeedEvent::factory()->for($this->account)->lootDrop()->create(['screenshot_path' => 'feed-screenshots/gone.png']);
    $outside = FeedEvent::factory()->for($this->account)->lootDrop()->create(['screenshot_path' => '../.env']);

    $this->actingAs($this->owner);
    $this->get(route('screenshots.file', $without))->assertNotFound();
    $this->get(route('screenshots.file', $missing))->assertNotFound();
    $this->get(route('screenshots.file', $outside))->assertNotFound();
    $this->get('/screenshots/999999')->assertNotFound();
});

it('answers a revalidation with 304', function () {
    $event = shotEvent($this->account);
    $etag = $this->get(route('screenshots.file', $event))->headers->get('ETag');

    $this->get(route('screenshots.file', $event), ['If-None-Match' => $etag])->assertStatus(304);
});

it('keeps screenshots off the public disk', function () {
    shotEvent($this->account);

    expect(Storage::disk('public')->allFiles())->toBe([])
        ->and(Storage::disk('local')->allFiles(ScreenshotStorage::DIRECTORY))->toHaveCount(1);
});

// --- URLs on the feed, the Log and the broadcast -----------------------------

it('gives the feed the route URL only for screenshots the viewer may see', function (?string $viewer, bool $seesHidden, bool $seesFlag) {
    $visible = shotEvent($this->account, ['occurred_at' => now()->subMinute()]);
    $hidden = shotEvent($this->account, ['screenshot_hidden' => true, 'occurred_at' => now()->subMinutes(2)]);

    if ($viewer !== null) {
        $this->actingAs($this->{$viewer});
    }

    $events = collect($this->get(route('feed.index'))->assertOk()->inertiaProps('events'))->keyBy('id');

    expect($events[$visible->id]['screenshot_url'])->toBe(route('screenshots.file', $visible->id))
        ->and($events[$hidden->id]['screenshot_url'])->toBe($seesHidden ? route('screenshots.file', $hidden->id) : null)
        ->and(array_key_exists('screenshot_hidden', $events[$hidden->id]))->toBe($seesFlag);

    if ($seesFlag) {
        expect($events[$hidden->id]['screenshot_hidden'])->toBeTrue()
            ->and($events[$visible->id]['screenshot_hidden'])->toBeFalse();
    }
})->with([
    'guest' => [null, false, false],
    'member' => ['member', false, false],
    'owner' => ['owner', true, true],
    'admin' => ['admin', true, true],
]);

it('gives the Log the route URL only for screenshots the viewer may see', function () {
    $visible = shotEvent($this->account, ['type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()->subMinute()]);
    $hidden = shotEvent($this->account, ['type' => FeedEvent::TYPE_PET, 'payload' => [], 'screenshot_hidden' => true, 'occurred_at' => now()->subMinutes(2)]);

    $rows = fn (?User $viewer) => collect(RecentEventResource::page(app(RecentEvents::class)->page($this->account, viewer: $viewer))['data'])
        ->keyBy('feed_event_id');

    $asMember = $rows($this->member);
    $asOwner = $rows($this->owner);

    expect($asMember[$visible->id]['screenshot_url'])->toBe(route('screenshots.file', $visible->id))
        ->and($asMember[$hidden->id]['screenshot_url'])->toBeNull()
        ->and($asMember[$hidden->id])->not->toHaveKey('screenshot_hidden')
        ->and($asOwner[$hidden->id]['screenshot_url'])->toBe(route('screenshots.file', $hidden->id))
        ->and($asOwner[$hidden->id]['screenshot_hidden'])->toBeTrue()
        ->and($asOwner[$visible->id]['screenshot_hidden'])->toBeFalse()
        ->and($rows($this->admin)[$hidden->id]['screenshot_url'])->not->toBeNull();
});

it('puts only visible screenshots on the public broadcast, even inside the owner\'s push', function () {
    $visible = shotEvent($this->account);
    $hidden = shotEvent($this->account, ['screenshot_hidden' => true]);

    $this->actingAs($this->owner);

    $visiblePayload = (new FeedEventCreated($visible->fresh()))->broadcastWith();
    $hiddenPayload = (new FeedEventCreated($hidden->fresh()))->broadcastWith();

    expect($visiblePayload['screenshot_url'])->toBe(route('screenshots.file', $visible->id))
        ->and($hiddenPayload['screenshot_url'])->toBeNull()
        ->and($hiddenPayload)->not->toHaveKey('screenshot_hidden');
});

// --- The storage migration ---------------------------------------------------

it('moves public screenshots to the private disk, drops missing ones and can re-run', function () {
    $public = Storage::disk('public');
    $public->put('feed-screenshots/1.png', 'first');
    $public->put('feed-screenshots/demo.png', 'shared');
    $public->put('feed-screenshots/orphan.png', 'orphan');

    $moved = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/1.png']);
    $sharedA = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/demo.png']);
    $sharedB = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/demo.png']);
    $missing = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/gone.png', 'screenshot_hidden' => true]);
    $already = shotEvent($this->account);
    $alreadyPath = $already->screenshot_path;

    $result = app(MoveScreenshotsToPrivateDisk::class)->run(pruneMissing: true);

    $private = Storage::disk('local');
    $paths = [$moved->fresh()->screenshot_path, $sharedA->fresh()->screenshot_path, $sharedB->fresh()->screenshot_path];

    expect($result)->toBe(['moved' => 3, 'missing' => [], 'pruned' => 1, 'unreferenced' => ['feed-screenshots/orphan.png']])
        ->and($private->get($paths[0]))->toBe('first')
        ->and($private->get($paths[1]))->toBe('shared')
        ->and($private->get($paths[2]))->toBe('shared')
        ->and($paths[1])->not->toBe($paths[2])
        ->and($paths[0])->toMatch('#^feed-screenshots/[A-Za-z0-9]{40}\.png$#')
        ->and($missing->fresh()->screenshot_path)->toBeNull()
        ->and($missing->fresh()->screenshot_hidden)->toBeFalse()
        ->and($already->fresh()->screenshot_path)->toBe($alreadyPath)
        // Each moved original is gone; the file nobody points at is only reported.
        ->and($public->allFiles())->toBe(['feed-screenshots/orphan.png']);

    // A second run changes nothing.
    expect(app(MoveScreenshotsToPrivateDisk::class)->run())->toBe(['moved' => 0, 'missing' => [], 'pruned' => 0, 'unreferenced' => ['feed-screenshots/orphan.png']])
        ->and([$moved->fresh()->screenshot_path, $sharedA->fresh()->screenshot_path, $sharedB->fresh()->screenshot_path])->toBe($paths)
        ->and($private->allFiles(ScreenshotStorage::DIRECTORY))->toHaveCount(4);
});

it('never checks or clears a row that already has a private name, even when its file cannot be found', function () {
    $path = ScreenshotStorage::DIRECTORY.'/'.Str::random(40).'.png';
    $event = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => $path, 'screenshot_hidden' => true]);

    expect(app(MoveScreenshotsToPrivateDisk::class)->run())->toMatchArray(['moved' => 0, 'missing' => [], 'pruned' => 0])
        ->and(app(MoveScreenshotsToPrivateDisk::class)->run(pruneMissing: true))->toMatchArray(['moved' => 0, 'missing' => [], 'pruned' => 0])
        ->and($event->fresh()->screenshot_path)->toBe($path)
        ->and($event->fresh()->screenshot_hidden)->toBeTrue();
});

it('reports and keeps a legacy row whose public file is missing unless asked to prune', function () {
    $event = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/41.png', 'screenshot_hidden' => true]);

    $this->artisan('screenshots:move-private')
        ->expectsOutputToContain('1 feed events point at a public screenshot that is missing (kept; rerun with --prune-missing to clear): '.$event->id)
        ->assertSuccessful();

    expect($event->fresh()->screenshot_path)->toBe('feed-screenshots/41.png');

    $this->artisan('screenshots:move-private', ['--prune-missing' => true])
        ->expectsOutputToContain('cleared 1 whose file was missing')
        ->assertSuccessful();

    expect($event->fresh()->screenshot_path)->toBeNull()
        ->and($event->fresh()->screenshot_hidden)->toBeFalse();
});

it('keeps the public file and the row when a copy fails', function () {
    Storage::disk('public')->put('feed-screenshots/7.png', 'original');
    $event = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/7.png']);
    $this->partialMock(ScreenshotStorage::class, fn ($mock) => $mock->shouldReceive('put')->andReturn(null));

    expect(fn () => app(MoveScreenshotsToPrivateDisk::class)->run())->toThrow(RuntimeException::class);

    expect($event->fresh()->screenshot_path)->toBe('feed-screenshots/7.png');
    Storage::disk('public')->assertExists('feed-screenshots/7.png');
});

it('keeps the public file and the row when a copy comes out short', function () {
    Storage::disk('public')->put('feed-screenshots/7.png', 'original');
    $event = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/7.png']);
    $this->partialMock(ScreenshotStorage::class, fn ($mock) => $mock->shouldReceive('put')
        ->andReturnUsing(fn () => Storage::disk('local')->put('feed-screenshots/short.png', 'orig') ? 'feed-screenshots/short.png' : null));

    expect(fn () => app(MoveScreenshotsToPrivateDisk::class)->run())->toThrow(RuntimeException::class);

    expect($event->fresh()->screenshot_path)->toBe('feed-screenshots/7.png');
    Storage::disk('public')->assertExists('feed-screenshots/7.png');
    Storage::disk('local')->assertMissing('feed-screenshots/short.png');
});

it('moves through the command and reports what it left', function () {
    Storage::disk('public')->put('feed-screenshots/3.png', 'late');
    Storage::disk('public')->put('feed-screenshots/orphan.png', 'orphan');
    $event = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/3.png']);

    $this->artisan('screenshots:move-private')
        ->expectsOutputToContain('Moved 1 screenshots')
        ->expectsOutputToContain('feed-screenshots/orphan.png')
        ->assertSuccessful();

    expect(Storage::disk('local')->get($event->fresh()->screenshot_path))->toBe('late');
});

it('fails the command without losing anything when a copy fails', function () {
    Storage::disk('public')->put('feed-screenshots/3.png', 'late');
    FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/3.png']);
    $this->partialMock(ScreenshotStorage::class, fn ($mock) => $mock->shouldReceive('put')->andReturn(null));

    $this->artisan('screenshots:move-private')->assertFailed();

    Storage::disk('public')->assertExists('feed-screenshots/3.png');
});

it('leaves the real disks alone when the migration runs under tests', function () {
    Storage::disk('public')->put('feed-screenshots/9.png', 'untouched');
    $event = FeedEvent::factory()->for($this->account)->create(['screenshot_path' => 'feed-screenshots/9.png']);

    (require database_path('migrations/2026_09_28_183043_move_feed_screenshots_to_private_disk.php'))->up();

    expect($event->fresh()->screenshot_path)->toBe('feed-screenshots/9.png');
    Storage::disk('public')->assertExists('feed-screenshots/9.png');
});

it('creates screenshot directories and files the web and deploy users can share', function () {
    $root = storage_path('framework/testing/screenshot-permissions');
    File::deleteDirectory($root);
    Storage::set('local', Storage::build([...config('filesystems.disks.local'), 'root' => $root]));
    $umask = umask(0);

    try {
        $path = app(ScreenshotStorage::class)->store(UploadedFile::fake()->image('shot.png', 8, 8));

        expect(fileperms($root.'/'.ScreenshotStorage::DIRECTORY) & 0777)->toBe(0775)
            ->and(fileperms($root.'/'.$path) & 0777)->toBe(0664);
    } finally {
        umask($umask);
        File::deleteDirectory($root);
    }
});

// --- Settings → Screenshots --------------------------------------------------

it('lists the viewer\'s own screenshots, newest first, with the filters', function () {
    $second = Account::factory()->for($this->owner)->create(['username' => 'Alt']);
    $newest = shotEvent($this->account, ['occurred_at' => now()->subMinute(), 'screenshot_hidden' => true]);
    shotEvent($second, ['occurred_at' => now()->subMinutes(2)]);
    FeedEvent::factory()->for($this->account)->pet()->create(['screenshot_path' => storedShot(), 'occurred_at' => now()->subMinutes(3)]);
    FeedEvent::factory()->for($this->account)->lootDrop()->create();
    shotEvent($this->memberAccount);

    $this->actingAs($this->owner)
        ->get(route('screenshots.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Screenshots/Index')
            ->has('screenshots.data', 3)
            ->where('screenshots.data.0.id', $newest->id)
            ->where('screenshots.data.0.account', ['id' => $this->account->id, 'username' => 'Shotter'])
            ->where('screenshots.data.0.type', FeedEvent::TYPE_LOOT_DROP)
            ->where('screenshots.data.0.url', route('screenshots.file', $newest->id))
            ->where('screenshots.data.0.hidden', true)
            ->has('screenshots.data.0.title')
            ->has('screenshots.data.0.occurred_at')
            ->where('screenshots.meta.total', 3)
            ->where('accounts', [['id' => $second->id, 'username' => 'Alt'], ['id' => $this->account->id, 'username' => 'Shotter']])
            ->has('types', 7)
            ->where('types.0', ['value' => 'loot_drop', 'label' => 'Loot drop'])
            ->where('filters', ['account' => null, 'type' => null]),
        );

    $this->actingAs($this->owner)
        ->get(route('screenshots.index', ['account' => $second->id]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('screenshots.data', 1)
            ->where('filters.account', $second->id));

    $this->actingAs($this->owner)
        ->get(route('screenshots.index', ['type' => 'pet']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('screenshots.data', 1)
            ->where('screenshots.data.0.type', 'pet')
            ->where('filters.type', 'pet'));

    // Someone else's account or an unknown type falls back to everything.
    $this->actingAs($this->owner)
        ->get(route('screenshots.index', ['account' => $this->memberAccount->id, 'type' => 'quest_complete']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('screenshots.data', 3)
            ->where('filters', ['account' => null, 'type' => null]));
});

it('pages the gallery 24 at a time', function () {
    foreach (range(1, 25) as $minutes) {
        shotEvent($this->account, ['occurred_at' => now()->subMinutes($minutes)]);
    }

    $this->actingAs($this->owner)
        ->get(route('screenshots.index', ['page' => 2]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('screenshots.data', 1)
            ->where('screenshots.meta.per_page', 24)
            ->where('screenshots.meta.last_page', 2));
});

it('requires a login for the gallery', function () {
    $this->get(route('screenshots.index'))->assertRedirect(route('login'));
});

it('hides and shows the owner\'s screenshots in bulk, skipping other people\'s', function () {
    $mine = [shotEvent($this->account), shotEvent($this->account)];
    $theirs = shotEvent($this->memberAccount);

    $this->actingAs($this->owner)
        ->from(route('screenshots.index'))
        ->patch(route('screenshots.update'), ['ids' => [$mine[0]->id, $mine[1]->id, $theirs->id], 'hidden' => true])
        ->assertRedirect(route('screenshots.index'))
        ->assertSessionHas('flash.banner', 'Hid 2 screenshots.');

    expect($mine[0]->fresh()->screenshot_hidden)->toBeTrue()
        ->and($mine[1]->fresh()->screenshot_hidden)->toBeTrue()
        ->and($theirs->fresh()->screenshot_hidden)->toBeFalse();

    $this->actingAs($this->owner)
        ->patch(route('screenshots.update'), ['ids' => [$mine[0]->id], 'hidden' => false])
        ->assertSessionHas('flash.banner', 'Showed 1 screenshot.');

    expect($mine[0]->fresh()->screenshot_hidden)->toBeFalse();
});

it('forbids a bulk action when none of the screenshots are the viewer\'s', function () {
    $theirs = shotEvent($this->memberAccount);

    $this->actingAs($this->owner)
        ->patch(route('screenshots.update'), ['ids' => [$theirs->id], 'hidden' => true])
        ->assertForbidden();
    $this->actingAs($this->owner)
        ->delete(route('screenshots.destroy'), ['ids' => [$theirs->id, 999999]])
        ->assertForbidden();

    expect($theirs->fresh()->screenshot_hidden)->toBeFalse()
        ->and($theirs->fresh()->screenshot_path)->not->toBeNull();
});

it('validates the bulk actions', function () {
    $this->actingAs($this->owner)
        ->patch(route('screenshots.update'), ['ids' => [], 'hidden' => true])
        ->assertSessionHasErrors('ids');
    $this->actingAs($this->owner)
        ->patch(route('screenshots.update'), ['ids' => ['x'], 'hidden' => 'maybe'])
        ->assertSessionHasErrors(['ids.0', 'hidden']);
    $this->actingAs($this->owner)
        ->delete(route('screenshots.destroy'), ['ids' => range(1, 101)])
        ->assertSessionHasErrors('ids');
});

it('deletes the owner\'s screenshots and their files, keeping the events', function () {
    $mine = shotEvent($this->account, ['screenshot_hidden' => true]);
    $path = $mine->screenshot_path;
    $theirs = shotEvent($this->memberAccount);

    $this->actingAs($this->owner)
        ->delete(route('screenshots.destroy'), ['ids' => [$mine->id, $theirs->id]])
        ->assertRedirect()
        ->assertSessionHas('flash.banner', 'Deleted 1 screenshot.');

    expect($mine->fresh())->not->toBeNull()
        ->and($mine->fresh()->screenshot_path)->toBeNull()
        ->and($mine->fresh()->screenshot_hidden)->toBeFalse()
        ->and($theirs->fresh()->screenshot_path)->not->toBeNull();
    Storage::disk('local')->assertMissing($path);
    Storage::disk('local')->assertExists($theirs->screenshot_path);
});

it('keeps an admin from bulk-editing someone else\'s screenshots', function () {
    $event = shotEvent($this->account);

    $this->actingAs($this->admin)
        ->delete(route('screenshots.destroy'), ['ids' => [$event->id]])
        ->assertForbidden();
});

it('throttles the screenshot writes in their own bucket', function () {
    $event = shotEvent($this->account);

    // Unbucketed writes (the Wise Old Man routes) leave the screenshots' allowance alone.
    foreach (range(1, 6) as $attempt) {
        $this->actingAs($this->owner)->put(route('wise-old-man.update', $this->account), ['show_on_progress' => false]);
    }

    foreach (range(1, 60) as $attempt) {
        $this->actingAs($this->owner)
            ->patch(route('screenshots.update'), ['ids' => [$event->id], 'hidden' => true])
            ->assertRedirect();
    }

    // Updates and deletes share the one bucket.
    $this->actingAs($this->owner)
        ->delete(route('screenshots.destroy'), ['ids' => [$event->id]])
        ->assertTooManyRequests();

    // The privacy switches keep their own allowance.
    $this->actingAs($this->owner)
        ->put(route('privacy.update', $this->account), ['hide_new_screenshots' => true])
        ->assertRedirect();
});

// --- Cleanup -----------------------------------------------------------------

it('deletes the file when an admin deletes the whole event', function () {
    $event = shotEvent($this->account);

    $this->actingAs($this->admin)->delete(route('feed.destroy', $event))->assertRedirect();

    expect(FeedEvent::find($event->id))->toBeNull();
    Storage::disk('local')->assertMissing($event->screenshot_path);
});

it('deletes every screenshot file when the instance is reset', function () {
    $events = [shotEvent($this->account), shotEvent($this->memberAccount)];

    app(ResetInstanceData::class)->run();

    expect(FeedEvent::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles(ScreenshotStorage::DIRECTORY))->toBe([]);
    Storage::disk('local')->assertMissing($events[0]->screenshot_path);
});

it('deletes the screenshots and resets the default when the owner deletes their user', function () {
    $event = shotEvent($this->account, ['screenshot_hidden' => true]);
    $this->account->forceFill(['hide_new_screenshots' => true])->save();
    $theirs = shotEvent($this->memberAccount);

    app(DeletesUsers::class)->delete($this->owner->fresh());

    expect($this->account->fresh()->user_id)->toBeNull()
        ->and($this->account->fresh()->hide_new_screenshots)->toBeFalse()
        ->and($event->fresh()->screenshot_path)->toBeNull()
        ->and($event->fresh()->screenshot_hidden)->toBeFalse()
        ->and($theirs->fresh()->screenshot_path)->not->toBeNull();
    Storage::disk('local')->assertMissing($event->screenshot_path);
});

it('deletes the screenshots and resets the default when an account is released', function () {
    $event = shotEvent($this->account);
    $this->account->forceFill(['hide_new_screenshots' => true])->save();

    $this->account->forceFill(['user_id' => null])->save();

    expect($this->account->fresh()->hide_new_screenshots)->toBeFalse()
        ->and($event->fresh()->screenshot_path)->toBeNull();
    Storage::disk('local')->assertMissing($event->screenshot_path);
});

it('deletes a released account\'s screenshots when it is claimed from the plugin', function () {
    $released = Account::factory()->create(['user_id' => null, 'username' => 'Released', 'account_hash' => null]);
    $event = shotEvent($released);
    DB::table('accounts')->where('id', $released->id)->update(['hide_new_screenshots' => true]);
    Sanctum::actingAs($this->member);

    $this->putJson('/api/plugin/heartbeat', [], [
        'X-Account-Hash' => 'hash-released',
        'X-Account-Username' => 'Released',
    ])->assertSuccessful();

    expect($released->fresh()->user_id)->toBe($this->member->id)
        ->and($released->fresh()->hide_new_screenshots)->toBeFalse()
        ->and($event->fresh()->screenshot_path)->toBeNull();
    Storage::disk('local')->assertMissing($event->screenshot_path);
});

it('deletes a released account\'s screenshots when it is claimed on registration', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    $released = Account::factory()->create(['user_id' => null, 'username' => 'Released']);
    $event = shotEvent($released);
    DB::table('accounts')->where('id', $released->id)->update(['hide_new_screenshots' => true]);

    $this->post('/register', [
        'name' => 'Claimer',
        'email' => 'claimer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        'icon_id' => 4151, // Abyssal whip, from the shared item catalogue
        'account_id' => $released->id,
    ])->assertRedirect();

    expect($released->fresh()->user_id)->not->toBeNull()
        ->and($released->fresh()->hide_new_screenshots)->toBeFalse()
        ->and($event->fresh()->screenshot_path)->toBeNull();
    Storage::disk('local')->assertMissing($event->screenshot_path);
})->skip(fn () => ! Features::enabled(Features::registration()), 'Registration is not enabled.');

it('deletes the screenshot files when an admin removes the account from the roster', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    $owner = tap(User::factory()->withPersonalTeam()->create())->assignRole(Roles::OWNER);
    $event = shotEvent($this->account);
    $theirs = shotEvent($this->memberAccount);

    $this->actingAs($owner)
        ->delete(route('admin.members.destroy', $this->account->id))
        ->assertRedirect();

    expect(Account::find($this->account->id))->toBeNull();
    Storage::disk('local')->assertMissing($event->screenshot_path);
    Storage::disk('local')->assertExists($theirs->screenshot_path);
});

it('keeps the files when the change of owner is rolled back', function () {
    $event = shotEvent($this->account);

    try {
        DB::transaction(function () {
            $this->account->forceFill(['user_id' => $this->member->id])->save();

            throw new RuntimeException('rolled back');
        });
    } catch (RuntimeException) {
    }

    expect($event->fresh()->screenshot_path)->not->toBeNull();
    Storage::disk('local')->assertExists($event->screenshot_path);
});

it('keeps the screenshots on an ordinary save', function () {
    $event = shotEvent($this->account);
    $this->account->forceFill(['hide_new_screenshots' => true])->save();
    $this->account->forceFill(['level' => 1500])->save();

    expect($this->account->fresh()->hide_new_screenshots)->toBeTrue()
        ->and($event->fresh()->screenshot_path)->not->toBeNull();
});

// --- Demo data ---------------------------------------------------------------

it('gives demo events placeholder screenshots idempotently, some hidden', function () {
    $this->owner->forceFill(['email' => 'zlimon@runemanager.com'])->save();
    FeedEvent::factory()->count(40)->for($this->account)->lootDrop()->create();
    FeedEvent::factory()->count(20)->for($this->memberAccount)->death()->create();
    FeedEvent::factory()->count(5)->for($this->memberAccount)->questComplete()->create();

    $this->seed(DemoScreenshotsSeeder::class);

    $owned = FeedEvent::query()->where('account_id', $this->account->id)->withScreenshot();

    expect($owned->count())->toBe(30)
        ->and((clone $owned)->where('screenshot_hidden', true)->count())->toBeGreaterThan(0)
        ->and(FeedEvent::query()->where('account_id', $this->memberAccount->id)->withScreenshot()->count())->toBe(12)
        ->and(FeedEvent::query()->ofType(FeedEvent::TYPE_QUEST_COMPLETE)->withScreenshot()->count())->toBe(0);

    $shot = FeedEvent::query()->withScreenshot()->first();
    expect(getimagesizefromstring(Storage::disk('local')->get($shot->screenshot_path))['mime'])->toBe('image/png');

    $this->seed(DemoScreenshotsSeeder::class);

    expect(FeedEvent::query()->withScreenshot()->count())->toBe(42)
        ->and(Storage::disk('local')->allFiles(ScreenshotStorage::DIRECTORY))->toHaveCount(42);
});
