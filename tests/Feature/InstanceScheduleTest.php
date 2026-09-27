<?php

use App\Helpers\SettingHelper;
use App\Support\Instance;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('reads the configured hiscore refresh cadence', function () {
    SettingHelper::setSetting('hiscore_refresh_minutes', 30, 'int');

    expect(Instance::hiscoreRefreshMinutes())->toBe(30);
    expect(Instance::hiscoreRefreshCron())->toBe('*/30 * * * *');
});

it('defaults to 60 minutes when unset', function () {
    expect(Instance::hiscoreRefreshMinutes())->toBe(60);
});

it('falls back to the default when the settings table is unavailable', function () {
    // routes/console.php builds the scheduler cron from this at load time, so it
    // runs on every artisan boot — including package:discover before the database
    // exists. It must not throw, just fall back to the default cadence.
    Schema::drop('settings');

    expect(Instance::hiscoreRefreshMinutes())->toBe(60);
    expect(Instance::hiscoreRefreshCron())->toBe('0 * * * *');
});

it('schedules exactly the expected commands and nothing else', function () {
    // Guards against scaffold leftovers (the stock `inspire` closure used to run
    // hourly in production) and against a new command being scheduled unnoticed.
    $events = collect(app(Schedule::class)->events());

    $scheduled = $events
        ->map(function ($event): string {
            // A scheduled closure has no $command at all — surface it by name so
            // the comparison below fails loudly instead of silently skipping it.
            if (! is_string($event->command) || $event->command === '') {
                return 'closure: '.$event->getSummaryForDisplay();
            }

            preg_match("/'artisan'\s+(\S+)/", $event->command, $matches);

            return $matches[1] ?? $event->command;
        })
        ->sort()
        ->values()
        ->all();

    expect($scheduled)->toBe([
        'collection-log:sync',
        'hiscores:sync',
        'items:sync',
        'resourcepack:update',
        'wom:sync',
    ]);
});

it('schedules the Wise Old Man sync daily without reading its toggle', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains((string) $event->command, 'wom:sync'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 5 * * *');
});
