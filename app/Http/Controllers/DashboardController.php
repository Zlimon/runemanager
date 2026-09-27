<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\CalendarEventResource;
use App\Http\Resources\FeedEventResource;
use App\Http\Resources\NewsPostResource;
use App\Models\Account;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\FeedEvent;
use App\Models\NewsPost;
use App\Services\Instance\InstanceStats;
use App\Services\WiseOldMan\WomLeaderboard;
use App\Support\Instance;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The instance homepage — a digest of what's happening: the latest news,
     * recent feed activity, the latest announcements, upcoming calendar events,
     * the top accounts by total level, headline counts and, with Wise Old Man on,
     * the week's top XP gainers. The Mongo-backed feed is lazy and the gainers
     * deferred, so the hero + relational widgets render without waiting on them.
     */
    public function index(InstanceStats $stats, WomLeaderboard $womLeaderboard): Response
    {
        return Inertia::render('Dashboard/Index', [
            'stats' => $stats->counts(),
            'news' => NewsPostResource::collection(
                NewsPost::with('user:id,name')->published()->limit(3)->get(),
            )->resolve(),
            'announcements' => AnnouncementResource::collection(
                Announcement::with('user:id,name')->active()->limit(5)->get(),
            )->resolve(),
            'upcoming' => CalendarEventResource::collection(
                CalendarEvent::with('user:id,name')->upcoming()->limit(5)->get(),
            )->resolve(),
            'topAccounts' => AccountResource::collection(
                Account::query()->with('user')->topByLevel(5)->get(),
            )->resolve(),
            'feed' => fn () => FeedEventResource::collectionWith(
                FeedEvent::query()->recent(8)->get(),
            )->resolve(),
            ...(Instance::womEnabled() ? [
                'topGainers' => Inertia::defer(fn () => $womLeaderboard->topGainers('week', 'exp', 5)),
            ] : []),
        ]);
    }
}
