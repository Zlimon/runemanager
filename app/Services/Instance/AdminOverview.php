<?php

namespace App\Services\Instance;

use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\FeedEvent;
use App\Models\NewsPost;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\Plugin\PluginTraffic;
use Illuminate\Support\Facades\Cache;

/**
 * Headline counts for the admin dashboard's overview. Read-only — see
 * InstanceStats for the public-facing counts this reuses (accounts) and
 * PluginTraffic for the plugin request-log figures this reuses.
 */
class AdminOverview
{
    /**
     * Every PluginTraffic read here runs a Mongo aggregation over the
     * request log, so the dashboard caches them briefly rather than
     * re-aggregating on every view. Short-lived since traffic figures are
     * meant to look current, unlike SystemHealth's storage sizes.
     */
    private const TRAFFIC_CACHE_SECONDS = 60;

    public function __construct(private InstanceStats $instanceStats, private PluginTraffic $traffic) {}

    /**
     * @return array{
     *     users: int, suspended_users: int, accounts: int,
     *     screenshots: int, hidden_screenshots: int,
     *     news_drafts: int, news_scheduled: int, news_published: int,
     *     upcoming_events: int, active_announcements: int, installed_packs: int,
     *     requests_24h: int, flagged_24h: int,
     * }
     */
    public function stats(): array
    {
        return [
            'users' => User::count(),
            'suspended_users' => User::query()->suspended()->count(),
            'accounts' => $this->instanceStats->counts()['accounts'],
            'screenshots' => FeedEvent::query()->withScreenshot()->count(),
            'hidden_screenshots' => FeedEvent::query()->withScreenshot()->hiddenScreenshot()->count(),
            'news_drafts' => NewsPost::query()->draft()->count(),
            'news_scheduled' => NewsPost::query()->scheduled()->count(),
            'news_published' => NewsPost::query()->published()->count(),
            'upcoming_events' => CalendarEvent::query()->upcoming()->count(),
            'active_announcements' => Announcement::query()->active()->count(),
            // See ResourcePack::installed() for what "installed" means here
            // versus InstallResourcePack::isReady() (which also checks disk).
            'installed_packs' => ResourcePack::query()->installed()->count(),
            'requests_24h' => Cache::remember(
                'admin-overview:requests_24h',
                self::TRAFFIC_CACHE_SECONDS,
                fn () => $this->traffic->requestCount('24h'),
            ),
            'flagged_24h' => Cache::remember(
                'admin-overview:flagged_24h',
                self::TRAFFIC_CACHE_SECONDS,
                fn () => $this->traffic->flaggedCount('24h'),
            ),
        ];
    }

    /**
     * The top of PluginTraffic's 24h suspicious-traffic heuristic, for the
     * dashboard's needs-attention list. See PluginTraffic::suspiciousUsers()
     * for what "suspicious" means and how reasons are ordered. The full list
     * is cached once (keyed on the window, not the limit) and sliced per
     * call, so different callers asking for different limits still share
     * one cached aggregation.
     *
     * @return array<int, array{user_id: ?int, name: ?string, ip: ?string, reasons: array<int, string>}>
     */
    public function suspiciousUsers(int $limit = 5): array
    {
        $all = Cache::remember(
            'admin-overview:suspicious-users:24h',
            self::TRAFFIC_CACHE_SECONDS,
            fn () => $this->traffic->suspiciousUsers('24h'),
        );

        return array_slice($all, 0, $limit);
    }
}
