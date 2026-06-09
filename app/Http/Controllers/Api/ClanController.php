<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Clan\SyncClanRoster;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SPEC §5.2 — clan endpoints pushed by the plugin:
 *   - update(): the logged-in player's own rank/title (keeps their role fresh).
 *   - roster(): the full clan roster, pushed by the owner, to pre-create the
 *     unclaimed accounts members later link to.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class ClanController extends Controller
{
    public function __construct(private SyncClanRoster $syncClanRoster) {}

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

        // Clan-rank → website-role mapping is deferred (SPEC §5.2, later).

        return response()->json(['data' => [
            'clan_rank' => $account->clan_rank,
            'clan_title' => $account->clan_title,
        ]]);
    }

    /**
     * Seed/refresh the clan roster. Requires the member-management permission
     * (the clan owner is the instance Owner), and only while in CLAN mode.
     */
    public function roster(Request $request): JsonResponse
    {
        if (! $request->user()->can(Roles::MANAGE_MEMBERS)) {
            abort(403, 'You do not have permission to seed the clan roster.');
        }

        $validated = $request->validate([
            'clan_name' => ['nullable', 'string', 'max:60'],
            'members' => ['present', 'array'],
            'members.*.username' => ['required', 'string', 'max:13'],
            'members.*.rank' => ['nullable', 'integer', 'between:-128,127'],
            'members.*.title' => ['nullable', 'string', 'max:60'],
        ]);

        if (! Instance::isClan()) {
            return response()->json(['data' => ['synced' => 0]]);
        }

        $synced = $this->syncClanRoster->ingest($validated['clan_name'] ?? null, $validated['members']);

        return response()->json(['data' => ['synced' => $synced]]);
    }
}
