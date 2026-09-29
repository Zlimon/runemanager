<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\Map\EventMap;
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
     * An optional `?focus=username` centres the map on that account's position
     * (e.g. from the account header's minimap): the owner's own last known
     * position, or anyone else's only while that account is still sharing
     * (Account::livePositionFor). `?x=&y=` (optional `&plane=`, 0-3) centres on a fixed
     * point instead — used by feed events that carry the position where they
     * happened. `focus` is `{x, y, plane}`.
     *
     * The events layer: `eventAccounts` are the accounts whose events the
     * viewer may show, and `?events=1,2` preselects some of them (ids the
     * viewer cannot pick are dropped). The events themselves come from
     * `map.events`.
     */
    public function index(Request $request, EventMap $eventMap): Response
    {
        $focus = null;
        if ($username = $request->string('focus')->trim()->value()) {
            $focus = Account::query()->where('username', $username)->first()?->livePositionFor($request->user());
        } elseif ($request->filled('x') && $request->filled('y')) {
            $plane = (int) $request->input('plane', 0);
            $focus = ['x' => (int) $request->input('x'), 'y' => (int) $request->input('y'), 'plane' => $plane >= 0 && $plane <= 3 ? $plane : 0];
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

        $eventAccounts = $eventMap->selectableAccounts($request->user());
        $events = $request->query('events');

        return Inertia::render('Map/Index', [
            'accounts' => $accounts,
            'focus' => $focus,
            'eventAccounts' => $eventAccounts,
            'eventAccountIds' => $eventMap->preselection($eventAccounts, is_string($events) ? $events : null),
        ]);
    }
}
