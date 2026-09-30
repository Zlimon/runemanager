<?php

use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

// The failed.driver in config/queue.php is database-uuids, so both
// queue:retry and the FailedJobProviderInterface address rows by uuid, not
// the table's numeric id — insertFailedJob() returns that uuid.
function insertFailedJob(string $queue = 'default'): string
{
    $uuid = (string) Str::uuid();

    DB::table('failed_jobs')->insert([
        'uuid' => $uuid,
        'connection' => 'database',
        'queue' => $queue,
        'payload' => json_encode(['uuid' => $uuid, 'displayName' => 'App\\Jobs\\ExampleJob', 'job' => 'Illuminate\\Queue\\CallQueuedHandler@call']),
        'exception' => "RuntimeException: boom\n#0 somewhere",
        'failed_at' => now(),
    ]);

    return $uuid;
}

it('forbids guests and non-admins from managing failed jobs', function () {
    $uuid = insertFailedJob();

    $this->post(route('admin.jobs.retry', $uuid))->assertRedirect(route('login'));
    $this->delete(route('admin.jobs.forget', $uuid))->assertRedirect(route('login'));

    $this->actingAs(adminUser(Roles::USER))
        ->post(route('admin.jobs.retry', $uuid))
        ->assertForbidden();
});

it('retries a single failed job by uuid', function () {
    $uuid = insertFailedJob();

    $this->actingAs(adminUser())
        ->post(route('admin.jobs.retry', $uuid))
        ->assertRedirect();

    // queue:retry re-pushes the payload onto the jobs table and forgets the
    // failed_jobs row once it has been requeued.
    expect(DB::table('failed_jobs')->count())->toBe(0);
    expect(DB::table('jobs')->count())->toBe(1);
});

it('retries every failed job when none is named', function () {
    insertFailedJob();
    insertFailedJob();

    $this->actingAs(adminUser())
        ->post(route('admin.jobs.retry'))
        ->assertRedirect();

    expect(DB::table('failed_jobs')->count())->toBe(0);
    expect(DB::table('jobs')->count())->toBe(2);
});

it('forgets a single failed job', function () {
    $uuid = insertFailedJob();
    $other = insertFailedJob();

    $this->actingAs(adminUser())
        ->delete(route('admin.jobs.forget', $uuid))
        ->assertRedirect();

    expect(DB::table('failed_jobs')->pluck('uuid')->all())->toBe([$other]);
});
