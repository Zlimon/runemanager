<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecentEventResource;
use App\Models\Account;
use App\Services\Accounts\RecentEvents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Further pages of the Adventurer's Log "Recent Events" list (JSON).
 */
class RecentEventsController extends Controller
{
    public function index(Request $request, Account $account, RecentEvents $recentEvents): JsonResponse
    {
        // Optional cursor; a malformed one falls back to the first page.
        $before = $request->query('before');

        return response()->json(
            RecentEventResource::page($recentEvents->page($account, is_string($before) ? $before : null, viewer: $request->user()), $request),
        );
    }
}
