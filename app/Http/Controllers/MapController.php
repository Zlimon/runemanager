<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    /**
     * Live Map — the initial set of accounts currently sharing a position.
     * Live movement then arrives over the private `map` broadcast channel
     * (AccountMoved). The payload shape matches the broadcast so the client can
     * upsert markers uniformly.
     *
     * An optional `?focus=username` centres the map on that account's last known
     * position (e.g. from the Account Show minimap), even if they aren't
     * currently sharing.
     */
    public function index(Request $request): Response
    {
        $focus = null;
        if ($username = $request->string('focus')->trim()->value()) {
            $account = Account::whereNotNull('world_x')->where('username', $username)->first();
            $focus = $account ? ['x' => $account->world_x, 'y' => $account->world_y] : null;
        }

        $accounts = Account::onMap()
            ->with('user')
            ->get()
            ->map(fn (Account $account): array => [
                'username' => $account->username,
                'account_type' => $account->account_type->value,
                'icon' => $account->userIcon,
                'level' => $account->level,
                'online' => true,
                'activity' => $account->activity,
                'x' => $account->world_x,
                'y' => $account->world_y,
                'plane' => $account->world_plane,
            ])
            ->all();

        return Inertia::render('Map/Index', [
            'accounts' => $accounts,
            'focus' => $focus,
        ]);
    }
}
