<?php

use App\Support\Instance;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// SPEC §12.4 — admin-configurable hiscores refresh cadence. console.php is
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
