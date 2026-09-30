<?php

namespace App\Providers;

use App\Models\User;
use App\Services\WiseOldMan\WiseOldManClient;
use App\Support\Roles;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Per-minute ceilings for the plugin API, applied per route as
     * `throttle:<name>` in routes/api.php. Each is a limit on abuse, not on
     * play: it sits at least 3x above the fastest cadence the RuneLite plugin
     * produces for a route. Every route has its own budget per user (per IP
     * when unauthenticated), so a busy push never starves another, and the
     * user's routes in one group together get PLUGIN_GROUP_FACTOR times that.
     * The key never includes the X-Account-Hash header: the client controls
     * it, so rotating hashes must not buy fresh budgets. Two alts played at
     * once on one login still fit, as each limit already has 3x headroom.
     * These throttles run before ResolvePluginAccount (see the middleware
     * priority in bootstrap/app.php), so a 429 never provisions an account.
     * The plugin logs a 429 and drops that push without retrying; snapshots
     * self-heal on the next change.
     *
     * - `plugin-live`: vitals (≤ 1/s, 60/min), position and status (≤ 1/1.5 s,
     *   40/min) and the 60 s heartbeat.
     * - `plugin-snapshot`: container PUTs fire on every ItemContainerChanged
     *   with no debounce, at most once per 0.6 s game tick (100/min) while
     *   banking or skilling; quests/diaries/CAs/clan change far less often.
     * - `plugin-events`: one POST per loot drop or unlock; multi-kill combat
     *   (chinning, barraging) bursts several drops per tick.
     * - `plugin-heavy`: requests with a server-side cost — screenshot and
     *   avatar uploads and the hiscores refresh (queues external fetches).
     *   The full-account push, avatar included, repeats on every world hop.
     * - `plugin-poll`: announcements (polled every 120 s, then one ack each)
     *   and `/me`/`/logout`.
     *
     * @var array<string, int>
     */
    public const PLUGIN_RATE_LIMITS = [
        'plugin-live' => 180,
        'plugin-snapshot' => 300,
        'plugin-events' => 300,
        'plugin-heavy' => 30,
        'plugin-poll' => 60,
    ];

    /** A user's whole limiter group allows this many times one route's limit. */
    public const PLUGIN_GROUP_FACTOR = 3;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Behind a TLS-terminating reverse proxy, PHP sees plain HTTP, so the URL
        // generator would emit http:// asset/links and the browser blocks them as
        // mixed content on an HTTPS page. Force https whenever the configured app
        // URL is https (production), leaving local http:// development untouched.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // The Owner holds every permission. Management abilities are
        // checked via Spatie permissions (manage instance/members/…). Clan/group
        // elevation of other users is layered on later.
        Gate::before(fn (User $user) => $user->hasRole(Roles::OWNER) ? true : null);

        // Paces the Wise Old Man sync jobs under WOM's own per-minute limit.
        RateLimiter::for('wiseoldman', fn () => Limit::perMinute(WiseOldManClient::perMinute()));

        foreach (self::PLUGIN_RATE_LIMITS as $name => $perMinute) {
            RateLimiter::for($name, function (Request $request) use ($name, $perMinute): array {
                $client = $request->user() ? 'user:'.$request->user()->id : 'ip:'.$request->ip();

                return [
                    Limit::perMinute($perMinute)->by($client.'|route:'.($request->route()?->getName() ?? $request->path())),
                    Limit::perMinute($perMinute * self::PLUGIN_GROUP_FACTOR)->by($client.'|group:'.$name),
                ];
            });
        }
    }
}
