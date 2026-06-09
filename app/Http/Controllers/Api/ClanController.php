<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Clan\SyncClanRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SPEC §5.2 — the plugin pushes the account's in-game clan name + rank whenever
 * the clan channel changes. Stored on the account and, in CLAN mode, mirrored
 * onto the owner's website role.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class ClanController extends Controller
{
    public function __construct(private SyncClanRole $syncClanRole) {}

    public function update(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validate([
            'clan_rank' => ['nullable', 'integer', 'between:-128,127'],
            'clan_title' => ['nullable', 'string', 'max:60'],
        ]);

        $account->forceFill([
            'clan_rank' => $validated['clan_rank'] ?? null,
            'clan_title' => $validated['clan_title'] ?? null,
        ])->save();

        $this->syncClanRole->forAccount($account);

        return response()->json(['data' => [
            'clan_rank' => $account->clan_rank,
            'clan_title' => $account->clan_title,
        ]]);
    }
}
