<?php

use App\Support\Instance;
use Illuminate\Support\Facades\Schedule;

// Admin-configurable hiscores refresh cadence. console.php is
// re-evaluated each `schedule:run`, so a changed setting takes effect next tick.
Schedule::command('hiscores:sync')
    ->cron(Instance::hiscoreRefreshCron())
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('collection-log:sync')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('resourcepack:update')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('items:sync')
    ->weekly()
    ->withoutOverlapping()
    ->runInBackground();

// Trails the hiscores sweep, whose fresh XP is what marks a WOM row stale.
// Whether the integration is on is checked inside the command, so this
// registration never reads the settings table.
Schedule::command('wom:sync')
    ->dailyAt('05:00')
    ->withoutOverlapping()
    ->runInBackground();
