<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeedScreenshotRequest;
use App\Models\Account;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Http\JsonResponse;

/**
 * A clean screenshot of the moment, uploaded by the plugin and attached to the
 * matching feed event by RecordFeedEvent::attachScreenshot(). A screenshot with
 * no qualifying event is quietly discarded rather than orphaned.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class FeedScreenshotController extends Controller
{
    public function store(StoreFeedScreenshotRequest $request, RecordFeedEvent $feed): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validated();

        $attached = $feed->attachScreenshot($account, $validated['type'], $request->file('image'));

        return response()->json(['data' => ['attached' => $attached]]);
    }
}
