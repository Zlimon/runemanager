<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeedEventResource;
use App\Models\FeedEvent;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    /**
     * SPEC §8 — reverse-chronological event stream for the instance.
     * Client uses Inertia's usePoll to refetch the `events` prop on an
     * interval; switch to broadcast/WebSockets later without changing
     * the prop contract.
     */
    public function index(): Response
    {
        $pageSize = (int) config('runemanager.feed.page_size', 50);

        return Inertia::render('Feed/Index', [
            'events' => fn () => FeedEventResource::collection(
                FeedEvent::query()->with('account:id,username,account_type')->recent($pageSize)->get(),
            )->resolve(),
            'pollIntervalMs' => (int) config('runemanager.feed.poll_interval_ms', 15_000),
        ]);
    }
}
