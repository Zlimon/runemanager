<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\CollectionHiscoreController;
use App\Http\Controllers\CollectionLogHiscoreController;
use App\Http\Controllers\CombatAchievementHiscoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiaryHiscoreController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\GroupBankController;
use App\Http\Controllers\ItemSearchController;
use App\Http\Controllers\LootHiscoreController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\OverallHiscoreController;
use App\Http\Controllers\SkillHiscoreController;
use App\Http\Controllers\StyleguideController;
use App\Http\Controllers\UserDarkModeController;
use App\Http\Controllers\UserResourcePackController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

// SPEC §8 — the live feed is intentionally public (no auth middleware).
Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');

// SPEC §10 — calendar viewing is public; mutation routes are auth-gated below.
Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');

// SPEC §9 — announcements are viewable in all modes; mutation is auth-gated below.
Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

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

    // SPEC §5.2 — shared Group Ironman bank (GROUP mode only; controller 404s otherwise).
    Route::get('/group-bank', [GroupBankController::class, 'index'])->name('group-bank.index');

    Route::prefix('/accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/{account}', [AccountController::class, 'show'])->name('accounts.show');
    });

    // SPEC §9/§10/§12 — management surfaces, each behind its own permission
    // (the Owner holds them all; clan/group elevation of other users is layered
    // on later).
    Route::prefix('/admin')->group(function () {
        Route::middleware('can:manage instance')->group(function () {
            Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
            Route::get('/settings/general', [AdminController::class, 'settings'])->name('admin.settings');
            Route::put('/settings/general', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
            Route::get('/settings/branding', [AdminController::class, 'branding'])->name('admin.branding');
            Route::get('/settings/feed', [AdminController::class, 'feedSync'])->name('admin.feed');
            Route::get('/settings/integrations', [AdminController::class, 'integrations'])->name('admin.integrations');
            Route::put('/config', [AdminController::class, 'updateConfig'])->name('admin.config.update');
            Route::post('/branding', [AdminController::class, 'updateBranding'])->name('admin.branding.update');
            Route::put('/integrations', [AdminController::class, 'updateIntegrations'])->name('admin.integrations.update');
            // SPEC §10 — calendar administration: manage every event and pick
            // which palette colours mark an event as important.
            Route::get('/calendar', [AdminController::class, 'calendar'])->name('admin.calendar');
            Route::put('/calendar/settings', [AdminController::class, 'updateCalendarSettings'])
                ->name('admin.calendar.settings');
            Route::get('/packs', [AdminController::class, 'packs'])->name('admin.packs');
            Route::post('/packs/install', [AdminController::class, 'installPack'])->name('admin.packs.install');
            Route::post('/packs/reinstall-vanilla', [AdminController::class, 'reinstallVanilla'])
                ->name('admin.packs.reinstall-vanilla');
            Route::delete('/packs/{pack}', [AdminController::class, 'destroyPack'])
                ->whereNumber('pack')->name('admin.packs.destroy');
        });

        Route::middleware('can:manage members')->group(function () {
            Route::get('/members', [AdminController::class, 'members'])->name('admin.members');
            Route::post('/members', [AdminController::class, 'storeMember'])->name('admin.members.store');
            Route::delete('/members/{account}', [AdminController::class, 'destroyMember'])
                ->whereNumber('account')->name('admin.members.destroy');
        });
    });

    // SPEC §8 — admins (manage instance) or the account's owner can delete feed
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

    // SPEC §10 — attendance is open to every member: the controller checks the
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

    // SPEC §6.2 — per-user appearance: browse packs + set a personal override
    // (instance-global default is set by the owner in admin settings).
    Route::get('/user/themes', [UserResourcePackController::class, 'index'])->name('themes.index');
    Route::post('/user/resource-pack/install', [UserResourcePackController::class, 'install'])
        ->name('user.resource-pack.install');
    Route::get('/user/resource-pack/status', [UserResourcePackController::class, 'status'])
        ->name('user.resource-pack.status');
    Route::get('/user/resource-pack/hub', [UserResourcePackController::class, 'hub'])
        ->name('user.resource-pack.hub');
    Route::delete('/user/resource-pack/{pack}', [UserResourcePackController::class, 'destroy'])
        ->whereNumber('pack')->name('user.resource-pack.destroy');

    Route::prefix('/hiscores')->group(function () {
        // SPEC §7.1 — Overall (total level + total XP).
        Route::get('/overall', [OverallHiscoreController::class, 'index'])->name('hiscores.overall.index');
        // SPEC §7 — Achievement Diaries (total tiers completed).
        Route::get('/diaries', [DiaryHiscoreController::class, 'index'])->name('hiscores.diaries.index');
        // SPEC §7 — Collection Log (total slots unlocked, via TempleOSRS).
        Route::get('/collection-log', [CollectionLogHiscoreController::class, 'index'])->name('hiscores.collection-log.index');
        // SPEC §7.1 — Combat Achievements (total points).
        Route::get('/combat-achievements', [CombatAchievementHiscoreController::class, 'index'])->name('hiscores.combat-achievements.index');
        Route::get('/skills/{skill}', [SkillHiscoreController::class, 'index'])->name('hiscores.skills.index');
        Route::get('/bosses/{collection}', [CollectionHiscoreController::class, 'index'])
            ->defaults('category', 'boss')
            ->name('hiscores.bosses.index');
        Route::get('/clues/{collection}', [CollectionHiscoreController::class, 'index'])
            ->defaults('category', 'clue')
            ->name('hiscores.clues.index');
        // SPEC §7 — loot directory (grouped sources) + per-source board.
        Route::get('/loot', [LootHiscoreController::class, 'index'])->name('hiscores.loot.index');
        Route::get('/loot/{type}/{source}', [LootHiscoreController::class, 'show'])
            ->where('source', '.*')
            ->name('hiscores.loot.show');
    });
});
