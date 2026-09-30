<?php

use App\Http\Middleware\EnsureInstanceConfigured;
use App\Http\Middleware\EnsureUserIsNotSuspended;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LimitPluginRequestsByIp;
use App\Http\Middleware\LogPluginRequest;
use App\Http\Middleware\ResolvePluginAccount;
use Illuminate\Auth\Middleware\Authorize;
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
            // Sign a suspended user out of any session that outlived the
            // suspension (before the setup funnel, which would redirect them).
            EnsureUserIsNotSuspended::class,
            // Funnel authenticated users to first-time setup until the
            // instance is configured (covers public pages too, e.g. /feed).
            EnsureInstanceConfigured::class,
        ]);
        $middleware->statefulApi();
        // Refuse a suspended user's still-valid token on every API route.
        $middleware->api(append: [EnsureUserIsNotSuspended::class.':sanctum']);
        // Record every plugin API request's metadata (first, so it sees every
        // failure below it, the IP ceiling's 429s included) for the admin
        // traffic monitor, then cap each IP before any token lookup.
        $middleware->api(prepend: [LogPluginRequest::class, LimitPluginRequestsByIp::class]);
        // Run the per-route `throttle:plugin-*` limits before the plugin's
        // account resolution, whose claim and auto-provision paths write to
        // the database: a throttled request must not create or claim anything.
        // Last in the priority list, so it also stays after SubstituteBindings
        // and the suspension check that follows it.
        $middleware->appendToPriorityList(Authorize::class, ResolvePluginAccount::class);
        // Both appearance cookies are read as plaintext server-side (Blade root +
        // Inertia share) to theme the first paint before any JS runs; neither
        // holds sensitive data.
        $middleware->encryptCookies(except: ['dark_mode', 'resource_pack']);
        $middleware->alias([
            'plugin.account' => ResolvePluginAccount::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
