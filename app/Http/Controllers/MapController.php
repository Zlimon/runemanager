<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    /**
     * Live Map — the initial set of accounts currently sharing a position.
     * Live movement then arrives over the private `map` broadcast channel
     * (AccountMoved). The payload shape matches the broadcast so the client can
     * upsert markers uniformly.
     */
    public function index(): Response
    {
        $accounts = Account::onMap()
            ->with('user')
            ->get()
            ->map(fn (Account $account): array => [
                'username' => $account->username,
                'account_type' => $account->account_type->value,
                'icon' => $account->userIcon,
                'level' => $account->level,
                'online' => true,
                'x' => $account->world_x,
                'y' => $account->world_y,
                'plane' => $account->world_plane,
            ])
            ->all();

        return Inertia::render('Map/Index', [
            'accounts' => $accounts,
        ]);
    }
}
