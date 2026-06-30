<?php

use App\Providers\AppServiceProvider;

it('forces the https scheme on generated URLs when the app URL is https', function () {
    config(['app.url' => 'https://runemanager.com']);

    // Re-boot the provider so it picks up the https app URL (the test app boots
    // with the default http://localhost).
    (new AppServiceProvider($this->app))->boot();

    expect(url('/login'))->toStartWith('https://')
        ->and(url('/build/assets/app.css'))->toStartWith('https://');
});

it('leaves the scheme alone for a plain http app URL (local dev)', function () {
    config(['app.url' => 'http://localhost']);

    (new AppServiceProvider($this->app))->boot();

    expect(url('/login'))->toStartWith('http://');
});
