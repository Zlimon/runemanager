<?php

use Illuminate\Support\Facades\Schema;

$migration = fn () => require database_path('migrations/2026_09_23_060252_drop_legacy_calendars_and_logs_tables.php');

it('leaves no calendars, logs or broadcasts table behind', function () {
    expect(Schema::hasTable('calendars'))->toBeFalse()
        ->and(Schema::hasTable('logs'))->toBeFalse()
        ->and(Schema::hasTable('broadcasts'))->toBeFalse();
});

it('restores the three tables as they were on rollback, empty', function () use ($migration) {
    $migration()->down();

    expect(Schema::getColumnListing('calendars'))
        ->toBe(['id', 'user_id', 'title', 'description', 'start_date', 'end_date', 'icon_id', 'event_color', 'created_at', 'updated_at'])
        ->and(Schema::getColumnListing('logs'))
        ->toBe(['id', 'user_id', 'account_id', 'category_id', 'action', 'data', 'total', 'created_at', 'updated_at'])
        ->and(Schema::getColumnListing('broadcasts'))
        ->toBe(['id', 'log_id', 'type', 'icon', 'message', 'created_at', 'updated_at']);

    $migration()->up();

    expect(Schema::hasTable('calendars'))->toBeFalse();
});
