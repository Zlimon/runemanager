<?php

use App\Helpers\SettingHelper;
use App\Support\Instance;
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
