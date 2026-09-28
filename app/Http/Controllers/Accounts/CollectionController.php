<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Resources\LootResource;
use App\Models\Account;
use App\Services\Accounts\AccountProfile;
use App\Services\CollectionLog\CollectionLogView;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
    public function show(Request $request, Account $account, AccountProfile $profile, CollectionLogView $collectionLogView): Response
    {
        return Inertia::render('Accounts/Collection/Show', [
            ...$profile->header($account, $request->user()),

            // External API — defer so the page paints first, then the client
            // pulls this in via a follow-up partial reload.
            'collectionLog' => Inertia::defer(fn () => $collectionLogView->build($account, $request->query('ccollection'))),

            // Loot — append-only history. Latest 25 drops with hydrated
            // item details for the recent-loot panel.
            'recentLoot' => fn () => LootResource::collectionWith($account->recentLoot(25))->resolve(),

            'freshness' => fn () => $profile->freshness($account, ['loot']),
        ]);
    }
}
