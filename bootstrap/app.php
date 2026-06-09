<?php

use App\Http\Middleware\EnsureInstanceConfigured;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolvePluginAccount;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            // SPEC §12 — funnel authenticated users to first-time setup until the
            // instance is configured (covers public pages too, e.g. /feed).
            EnsureInstanceConfigured::class,
        ]);
        $middleware->statefulApi();
        $middleware->alias([
            'plugin.account' => ResolvePluginAccount::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
