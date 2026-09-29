<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecentEventResource;
use App\Models\Account;
use App\Services\Accounts\RecentEvents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Further pages of the Adventurer's Log "Recent Events" list (JSON), and the
 * first page of its "Game events" / "Records" tabs (`?filter=game|records`).
 */
class RecentEventsController extends Controller
{
    public function index(Request $request, Account $account, RecentEvents $recentEvents): JsonResponse
    {
        // Optional cursor and filter; a malformed cursor falls back to the
        // first page, and an unknown filter to every kind of event.
        $before = $request->query('before');
        $filter = $request->query('filter');

        return response()->json(
            RecentEventResource::page($recentEvents->page(
                $account,
                is_string($before) ? $before : null,
                viewer: $request->user(),
                filter: is_string($filter) ? $filter : null,
            ), $request),
        );
    }
}
