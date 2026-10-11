<?php

use App\Models\ResourcePack;

it('excludes stub rows still at version pending', function () {
    makePack('pack-ready');
    makePack('pack-stub', ['version' => 'pending']);

    expect(ResourcePack::query()->installed()->pluck('name')->all())->toBe(['pack-ready']);
});
