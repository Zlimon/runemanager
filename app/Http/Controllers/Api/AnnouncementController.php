<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Account;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * In-game announcement delivery (SPEC §9.2). The plugin polls for active
 * announcements the resolved account hasn't seen, displays them, then acks each
 * so it isn't shown again. The Account is resolved by the plugin.account
 * middleware.
 */
class AnnouncementController extends Controller
{
    /**
     * Active announcements this account has not yet acknowledged. Returned as a
     * bare array (not wrapped in `data`) for simple plugin parsing.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $announcements = Announcement::query()
            ->active()
            ->whereDoesntHave('acknowledgedBy', fn ($query) => $query->whereKey($account->id))
            ->get();

        return response()->json(AnnouncementResource::collection($announcements)->resolve());
    }

    public function acknowledge(Request $request, Announcement $announcement): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $announcement->acknowledgedBy()->syncWithoutDetaching([$account->id]);

        return response()->json(['data' => ['acknowledged' => true]]);
    }
}
