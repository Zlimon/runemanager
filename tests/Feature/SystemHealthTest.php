<?php

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\CollectionLog;
use App\Services\Instance\SystemHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->health = new SystemHealth;
});

it('reports zero failed and pending jobs on a clean instance', function () {
    $report = $this->health->report();

    expect($report['failed_jobs'])->toBe(['count' => 0, 'latest' => []]);
    expect($report['pending_jobs']['driver'])->toBe(config('queue.default'));
});

it('counts failed jobs and returns the latest 5 with the exception first line', function () {
    foreach (range(1, 7) as $i) {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => "RuntimeException: boom {$i}\n#0 somewhere",
            'failed_at' => now()->subMinutes(7 - $i),
        ]);
    }

    $report = $this->health->report();

    expect($report['failed_jobs']['count'])->toBe(7);
    expect($report['failed_jobs']['latest'])->toHaveCount(5);
    // Most recently failed first.
    expect($report['failed_jobs']['latest'][0]['message'])->toBe('RuntimeException: boom 7');
    expect($report['failed_jobs']['latest'][0])->toHaveKeys(['id', 'uuid', 'queue', 'message', 'failed_at']);
});

it('counts pending database-queue jobs when the queue driver is database', function () {
    config(['queue.default' => 'database']);

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => '{}',
        'attempts' => 0,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $report = $this->health->report();

    expect($report['pending_jobs'])->toBe(['count' => 1, 'driver' => 'database']);
});

it('does not query the jobs table for a non-database queue driver', function () {
    config(['queue.default' => 'sync']);

    $report = $this->health->report();

    expect($report['pending_jobs'])->toBe(['count' => null, 'driver' => 'sync']);
});

it('reports the most recent hiscore and collection log sync timestamps', function () {
    $account = Account::factory()->create();

    AccountHiscore::query()->create([
        'account_id' => $account->id,
        'entries' => ['skills' => [], 'activities' => []],
        'fetched_at' => now()->subDay(),
    ]);
    $latestHiscore = AccountHiscore::query()->create([
        'account_id' => $account->id,
        'entries' => ['skills' => [], 'activities' => []],
        'fetched_at' => now(),
    ]);

    CollectionLog::query()->create([
        'account_id' => $account->id,
        'obtained' => 1,
        'total' => 10,
        'categories_finished' => 0,
        'categories_available' => 1,
        'items' => [],
        'fetched_at' => now(),
    ]);

    $report = $this->health->report();

    expect($report['sync']['hiscores_last_synced_at'])
        ->toBe($latestHiscore->fetched_at->toIso8601String());
    expect($report['sync']['collection_log_last_synced_at'])->not->toBeNull();
});

it('reports null sync timestamps when nothing has synced yet', function () {
    $report = $this->health->report();

    expect($report['sync'])->toBe([
        'hiscores_last_synced_at' => null,
        'collection_log_last_synced_at' => null,
    ]);
});

it('sums screenshot storage usage from the private disk', function () {
    // The private `local` disk is faked for every Feature test (tests/Pest.php),
    // backed by a real temp directory that SystemHealth reads sizes from.
    Storage::disk('local')->put('feed-screenshots/one.png', str_repeat('a', 100));
    Storage::disk('local')->put('feed-screenshots/two.png', str_repeat('b', 50));

    $report = $this->health->report();

    expect($report['storage']['screenshots_bytes'])->toBe(150);
});

it('reports null storage usage when the directory does not exist', function () {
    // A fresh fake disk has no feed-screenshots directory yet.
    $report = $this->health->report();

    expect($report['storage']['screenshots_bytes'])->toBeNull();
});

it('caches storage usage so a second read does not re-walk the directory', function () {
    Storage::disk('local')->put('feed-screenshots/one.png', str_repeat('a', 100));

    expect($this->health->report()['storage']['screenshots_bytes'])->toBe(100);

    // Within the cache window, a file added after the first read is not
    // reflected — proves the size came from cache, not a fresh walk.
    Storage::disk('local')->put('feed-screenshots/two.png', str_repeat('b', 50));

    expect($this->health->report()['storage']['screenshots_bytes'])->toBe(100);
});

it('never throws when the underlying tables or directories are unavailable', function () {
    // Dropping the failed_jobs table simulates a migration that hasn't run yet.
    DB::statement('DROP TABLE IF EXISTS failed_jobs');

    $report = $this->health->report();

    expect($report['failed_jobs'])->toBe(['count' => 0, 'latest' => []]);
});
