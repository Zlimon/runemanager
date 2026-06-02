<?php

use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

function packRow(string $name, string $version): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => $name,
        'alias' => ucfirst($name),
        'version' => $version,
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => false,
    ]);
}

it('does nothing when there are no installed packs', function () {
    $this->artisan('resourcepack:update')
        ->expectsOutputToContain('No resource packs to check.')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

it('does not queue a re-install when upstream matches local', function () {
    packRow('sample-vanilla', '1.0.0');

    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('latestUpstreamVersion')
        ->with('sample-vanilla')->andReturn('1.0.0'));

    $this->artisan('resourcepack:update')
        ->expectsOutputToContain('Checked 1, queued 0 for re-install.')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

it('queues a re-install when upstream is ahead of local', function () {
    packRow('sample-vanilla', '1.0.0');

    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('latestUpstreamVersion')
        ->with('sample-vanilla')->andReturn('1.1.0'));

    $this->artisan('resourcepack:update')
        ->expectsOutputToContain('Checked 1, queued 1 for re-install.')
        ->assertSuccessful();

    Queue::assertPushed(FetchResourcePackJob::class, fn ($job) => $job->packName === 'sample-vanilla');
});

it('skips packs whose upstream is unreachable (null version)', function () {
    packRow('sample-vanilla', '1.0.0');

    $this->mock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('latestUpstreamVersion')
        ->with('sample-vanilla')->andReturn(null));

    $this->artisan('resourcepack:update')
        ->expectsOutputToContain('upstream unreachable, skipping')
        ->expectsOutputToContain('queued 0')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

it('runs only the named pack when an argument is given', function () {
    packRow('alpha', '1.0.0');
    packRow('beta', '1.0.0');

    $mock = $this->mock(InstallResourcePack::class);
    $mock->shouldReceive('latestUpstreamVersion')->with('beta')->andReturn('2.0.0');
    $mock->shouldNotReceive('latestUpstreamVersion')->with('alpha');

    $this->artisan('resourcepack:update', ['name' => 'beta'])
        ->expectsOutputToContain('Checked 1, queued 1')
        ->assertSuccessful();

    Queue::assertPushed(FetchResourcePackJob::class, fn ($job) => $job->packName === 'beta');
});

it('registers resourcepack:update to run daily in the scheduler', function () {
    $events = collect(app(Schedule::class)->events());
    $event = $events->first(fn ($e) => str_contains($e->command ?? '', 'resourcepack:update'));

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('0 0 * * *');
});
