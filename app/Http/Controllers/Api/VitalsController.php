<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountVitalsUpdated;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VitalsController extends Controller
{
    /**
     * Live status-orb push from the RuneLite plugin (current/max hitpoints and
     * prayer, run energy and special attack as 0-100 percentages). Stored on the
     * account and broadcast so an open profile's orbs update live.
     *
     * The Account is resolved by the plugin.account middleware.
     */
    public function update(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validate([
            'hitpoints' => ['required', 'integer', 'min:0'],
            'hitpoints_max' => ['required', 'integer', 'min:1'],
            'prayer' => ['required', 'integer', 'min:0'],
            'prayer_max' => ['required', 'integer', 'min:1'],
            'run_energy' => ['required', 'integer', 'min:0', 'max:100'],
            'special_attack' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $account->forceFill([...$validated, 'vitals_updated_at' => now()])->save();

        broadcast(new AccountVitalsUpdated($account));

        return response()->json(['data' => ['vitals_updated_at' => $account->vitals_updated_at->toIso8601String()]]);
    }
}
