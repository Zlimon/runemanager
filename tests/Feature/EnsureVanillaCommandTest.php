<?php

use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('downloads Vanilla when the files are missing', function () {
    $this->mock(InstallResourcePack::class, function ($mock) {
        $mock->shouldReceive('isInstalled')->with('sample-vanilla')->once()->andReturn(false);
        $mock->shouldReceive('install')->with('sample-vanilla')->once();
        $mock->shouldNotReceive('ensureVanilla');
    });

    $this->artisan('resourcepack:ensure-vanilla')->assertSuccessful();
});

it('only ensures the row when the files are already present', function () {
    $this->mock(InstallResourcePack::class, function ($mock) {
        $mock->shouldReceive('isInstalled')->with('sample-vanilla')->once()->andReturn(true);
        $mock->shouldReceive('ensureVanilla')->once();
        $mock->shouldNotReceive('install');
    });

    $this->artisan('resourcepack:ensure-vanilla')->assertSuccessful();
});

it('never fails the deploy when the install throws', function () {
    $this->mock(InstallResourcePack::class, function ($mock) {
        $mock->shouldReceive('isInstalled')->andReturn(false);
        $mock->shouldReceive('install')->andThrow(new RuntimeException('hub unreachable'));
    });

    $this->artisan('resourcepack:ensure-vanilla')->assertSuccessful();
});
