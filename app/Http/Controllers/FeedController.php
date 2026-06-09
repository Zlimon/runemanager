<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeedEventResource;
use App\Models\FeedEvent;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    /**
     * SPEC §8 — reverse-chronological event stream for the instance. Seeds the
     * page; new events arrive live on the public `feed` broadcast channel
     * (FeedEventCreated), so there's no polling.
     */
    public function index(): Response
    {
        $pageSize = (int) config('runemanager.feed.page_size', 50);

        return Inertia::render('Feed/Index', [
            'events' => fn () => FeedEventResource::collectionWith(
                FeedEvent::query()->with('account:id,username,account_type')->recent($pageSize)->get(),
            )->resolve(),
        ]);
    }
}
