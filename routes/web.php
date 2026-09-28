<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Accounts;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\GroupBankController;
use App\Http\Controllers\Hiscores;
use App\Http\Controllers\ItemSearchController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\News;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\StyleguideController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\UserDarkModeController;
use App\Http\Controllers\UserResourcePackController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WiseOldManSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

// The live feed is intentionally public (no auth middleware).
Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');

// Calendar viewing is public; mutation routes are auth-gated below.
Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');

// Announcements are viewable in all modes; mutation is auth-gated below.
Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

// News is readable by anyone in every mode, like announcements above: someone
// weighing up joining a clan should be able to read its news first. Writing is
// gated by `can:manage news` in the auth group below.
//
// `manage/` sits in the middle of the editor paths on purpose. A post is bound
// by slug, so a two-segment `/news/create` would be swallowed by the
// `/news/{newsPost}` route below and resolve as an article called "create".
Route::get('/news', [News\ArticleController::class, 'index'])->name('news.index');
Route::get('/news/{newsPost}', [News\ArticleController::class, 'show'])->name('news.show');

// Wise Old Man progress. Registered outside the auth group because the owner
// may make it public; the controller 404s while the integration is off and
// sends guests to login while it is members-only.
Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');

// Light/dark preference — public so the on-page toggle works on the auth screens
// too (guests persist to a cookie; logged-in users to their account).
Route::put('/dark-mode', [UserDarkModeController::class, 'update'])->name('dark-mode.update');

// Resource-pack preference — public for the same reason: a guest must be able to
// opt out of packs to reach the untextured interface, which is the only one that
// offers the light/dark toggle. Guests persist to a cookie, users to their account.
Route::put('/user/resource-pack', [UserResourcePackController::class, 'update'])
    ->name('user.resource-pack.update');

// Component gallery for validating resource-pack textures against every
// installed pack. Open locally on purpose: a login between a developer (or a
// browser check) and the page whose entire job is to be looked at defeats the
// point, and there is nothing behind it but the instance's own sprites.
// Everywhere else it sits with the rest of the management surfaces.
Route::middleware(app()->environment('local') ? [] : [
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'can:manage instance',
])->group(function () {
    Route::get('/styleguide', [StyleguideController::class, 'index'])->name('styleguide');
    // Plain JSON rather than a deferred Inertia prop: fetching it must not be an
    // Inertia visit. A visit re-sends the shared props, and DarkModeToggle
    // watches page.props.theme to restamp <html data-theme> — which would undo
    // the pack the switcher is previewing every time the panel loaded.
    Route::get('/styleguide/sprites', [StyleguideController::class, 'sprites'])
        ->name('styleguide.sprites');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Live Map — see where accounts are in real time (positions over websockets).
    Route::get('/map', [MapController::class, 'index'])->name('map.index');

    // Shared Group Ironman bank (GROUP mode only; controller 404s otherwise).
    Route::get('/group-bank', [GroupBankController::class, 'index'])->name('group-bank.index');

    Route::prefix('/accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/{account}', [Accounts\LogController::class, 'show'])->name('accounts.show');
        Route::get('/{account}/gains', [Accounts\GainsController::class, 'show'])->name('accounts.gains');
        Route::get('/{account}/items', [Accounts\ItemsController::class, 'show'])->name('accounts.items');
        Route::get('/{account}/collection', [Accounts\CollectionController::class, 'show'])->name('accounts.collection');
        Route::get('/{account}/events', [Accounts\RecentEventsController::class, 'index'])->name('accounts.events');
    });

    // Management surfaces, each behind its own permission
    // (the Owner holds them all; clan/group elevation of other users is layered
    // on later).
    Route::prefix('/admin')->group(function () {
        Route::middleware('can:manage instance')->group(function () {
            Route::get('/', [Admin\DashboardController::class, 'index'])->name('admin.dashboard');
            Route::get('/settings/general', [Admin\GeneralSettingsController::class, 'edit'])->name('admin.settings');
            Route::put('/settings/general', [Admin\GeneralSettingsController::class, 'update'])->name('admin.settings.update');
            Route::get('/settings/branding', [Admin\BrandingController::class, 'edit'])->name('admin.branding');
            Route::get('/settings/feed', [Admin\FeedSyncController::class, 'edit'])->name('admin.feed');
            Route::get('/settings/integrations', [Admin\IntegrationsController::class, 'edit'])->name('admin.integrations');
            Route::put('/config', [Admin\FeedSyncController::class, 'update'])->name('admin.config.update');
            Route::post('/branding', [Admin\BrandingController::class, 'update'])->name('admin.branding.update');
            Route::put('/integrations', [Admin\IntegrationsController::class, 'update'])->name('admin.integrations.update');
            // Calendar administration: manage every event and pick
            // which palette colours mark an event as important.
            Route::get('/calendar', [Admin\CalendarController::class, 'index'])->name('admin.calendar');
            Route::put('/calendar/settings', [Admin\CalendarController::class, 'update'])
                ->name('admin.calendar.settings');
            Route::get('/packs', [Admin\ResourcePackController::class, 'index'])->name('admin.packs');
            Route::post('/packs/install', [Admin\ResourcePackController::class, 'store'])->name('admin.packs.install');
            Route::post('/packs/reinstall-vanilla', [Admin\ResourcePackController::class, 'reinstallVanilla'])
                ->name('admin.packs.reinstall-vanilla');
            Route::delete('/packs/{pack}', [Admin\ResourcePackController::class, 'destroy'])
                ->whereNumber('pack')->name('admin.packs.destroy');
        });

        Route::middleware('can:manage members')->group(function () {
            Route::get('/members', [Admin\MemberController::class, 'index'])->name('admin.members');
            Route::post('/members', [Admin\MemberController::class, 'store'])->name('admin.members.store');
            Route::delete('/members/{account}', [Admin\MemberController::class, 'destroy'])
                ->whereNumber('account')->name('admin.members.destroy');
        });
    });

    // Admins (manage instance) or the account's owner can delete feed
    // entries; the controller enforces ownership.
    Route::delete('/feed/{feedEvent}', [FeedController::class, 'destroy'])
        ->whereNumber('feedEvent')->name('feed.destroy');
    // Owner-only: pin/unpin an entry to the account's achievement gallery.
    Route::put('/feed/{feedEvent}/pin', [FeedController::class, 'togglePin'])
        ->whereNumber('feedEvent')->name('feed.pin');

    Route::middleware('can:manage calendar')->group(function () {
        Route::post('/calendar', [CalendarEventController::class, 'store'])->name('calendar.store');
        Route::put('/calendar/{calendarEvent}', [CalendarEventController::class, 'update'])
            ->name('calendar.update');
        Route::delete('/calendar/{calendarEvent}', [CalendarEventController::class, 'destroy'])
            ->name('calendar.destroy');
    });

    // Attendance is open to every member: the controller checks the
    // named account belongs to the caller, so no permission gate here.
    Route::post('/calendar/{calendarEvent}/rsvp', [CalendarEventController::class, 'rsvp'])
        ->name('calendar.rsvp');
    Route::delete('/calendar/{calendarEvent}/rsvp', [CalendarEventController::class, 'destroyRsvp'])
        ->name('calendar.rsvp.destroy');

    // Typeahead over the static item catalogue, for the calendar's item picker.
    Route::get('/items/search', ItemSearchController::class)->name('items.search');

    Route::middleware('can:manage announcements')->group(function () {
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])
            ->name('announcements.destroy');
    });

    Route::middleware('can:manage news')->group(function () {
        Route::get('/news/manage/new', [News\EditorController::class, 'create'])->name('news.create');
        Route::get('/news/manage/{newsPost}/edit', [News\EditorController::class, 'edit'])->name('news.edit');
        Route::post('/news', [News\EditorController::class, 'store'])->name('news.store');
        // POST, not PUT: the header image makes this a multipart submission, and
        // Inertia spoofs the method for those.
        Route::post('/news/{newsPost}', [News\EditorController::class, 'update'])->name('news.update');
        Route::delete('/news/{newsPost}', [News\EditorController::class, 'destroy'])->name('news.destroy');
        // Inline images for the editor, answered with JSON rather than a visit.
        Route::post('/news/manage/images', [News\ImageController::class, 'store'])->name('news.images.store');
    });

    // Per-user appearance: browse packs + set a personal override
    // (instance-global default is set by the owner in admin settings).
    Route::get('/user/themes', [ThemeController::class, 'index'])->name('themes.index');
    Route::post('/user/resource-pack/install', [UserResourcePackController::class, 'install'])
        ->name('user.resource-pack.install');
    Route::get('/user/resource-pack/status', [UserResourcePackController::class, 'status'])
        ->name('user.resource-pack.status');
    Route::get('/user/resource-pack/hub', [UserResourcePackController::class, 'hub'])
        ->name('user.resource-pack.hub');
    Route::delete('/user/resource-pack/{pack}', [UserResourcePackController::class, 'destroy'])
        ->whereNumber('pack')->name('user.resource-pack.destroy');

    // Per-account Wise Old Man preferences; the controller checks ownership and
    // 404s while the integration is off.
    Route::get('/settings/wise-old-man', [WiseOldManSettingsController::class, 'index'])->name('wise-old-man.index');
    // Throttled: tracking queues a WOM update, and both write per click.
    Route::post('/settings/wise-old-man/{account}/track', [WiseOldManSettingsController::class, 'track'])
        ->middleware('throttle:6,1')->name('wise-old-man.track');
    Route::put('/settings/wise-old-man/{account}', [WiseOldManSettingsController::class, 'update'])
        ->middleware('throttle:6,1')->name('wise-old-man.update');

    Route::prefix('/hiscores')->group(function () {
        // Overall (total level + total XP).
        Route::get('/overall', [Hiscores\OverallController::class, 'index'])->name('hiscores.overall.index');
        // Achievement Diaries (total tiers completed).
        Route::get('/diaries', [Hiscores\DiaryController::class, 'index'])->name('hiscores.diaries.index');
        // Collection Log (total slots unlocked, via TempleOSRS).
        Route::get('/collection-log', [Hiscores\CollectionLogController::class, 'index'])->name('hiscores.collection-log.index');
        // Combat Achievements (total points).
        Route::get('/combat-achievements', [Hiscores\CombatAchievementController::class, 'index'])->name('hiscores.combat-achievements.index');
        Route::get('/skills/{skill}', [Hiscores\SkillController::class, 'index'])->name('hiscores.skills.index');
        // Bosses and clues render the same board from the same activity
        // entries; only the URL differs, so neither passes a category.
        Route::get('/bosses/{collection}', [Hiscores\CollectionController::class, 'index'])
            ->name('hiscores.bosses.index');
        Route::get('/clues/{collection}', [Hiscores\CollectionController::class, 'index'])
            ->name('hiscores.clues.index');
        // Loot directory (grouped sources) + per-source board.
        Route::get('/loot', [Hiscores\LootController::class, 'index'])->name('hiscores.loot.index');
        Route::get('/loot/{type}/{source}', [Hiscores\LootController::class, 'show'])
            ->where('source', '.*')
            ->name('hiscores.loot.show');
    });
});
