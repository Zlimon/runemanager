<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankResource;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\LootingBagResource;
use App\Models\Account;
use App\Services\Accounts\AccountProfile;
use Inertia\Inertia;
use Inertia\Response;

class ItemsController extends Controller
{
    public function show(Account $account, AccountProfile $profile): Response
    {
        return Inertia::render('Accounts/Items/Show', [
            ...$profile->header($account),

            'inventory' => fn () => $account->inventory
                ? (new InventoryResource($account->inventory))->resolve()
                : null,

            'bank' => fn () => $account->bank
                ? (new BankResource($account->bank))->resolve()
                : null,

            'lootingBag' => fn () => $account->lootingBag
                ? (new LootingBagResource($account->lootingBag))->resolve()
                : null,

            'freshness' => fn () => $profile->freshness($account, ['inventory', 'looting_bag', 'bank', 'equipment']),
        ]);
    }
}
