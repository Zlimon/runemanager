<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeartbeatController extends Controller
{
    /**
     * Presence ping from the RuneLite plugin (sent roughly once a minute while
     * logged in). Stamps last_seen_at; "online" is derived from it on read.
     *
     * The Account is resolved by the plugin.account middleware.
     */
    public function update(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $account->forceFill(['last_seen_at' => now()])->save();

        return response()->json(['data' => ['last_seen_at' => $account->last_seen_at->toIso8601String()]]);
    }
}
