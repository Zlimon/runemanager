<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\CalendarEventResource;
use App\Http\Resources\FeedEventResource;
use App\Models\Account;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\FeedEvent;
use App\Services\Instance\InstanceStats;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The instance homepage — a digest of what's happening: recent feed
     * activity, the latest announcements, upcoming calendar events, the top
     * accounts by total level, and headline counts. The Mongo-backed feed is
     * deferred so the hero + relational widgets render without waiting on it.
     */
    public function index(InstanceStats $stats): Response
    {
        return Inertia::render('Dashboard/Index', [
            'stats' => $stats->counts(),
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
                FeedEvent::query()->with('account:id,username,account_type')->recent(8)->get(),
            )->resolve(),
        ]);
    }
}
