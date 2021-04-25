<?php

namespace App\Http\Controllers\Api;

use App\Collection;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountBossResource;
use App\Http\Resources\CollectionResource;
use Illuminate\Http\Request;

class AccountBossController extends Controller
{
    public function index($accountUsername)
    {
        return new AccountBossResource(Helper::getAccountFromUsername($accountUsername));
    }

    public function show($accountUsername, $bossName)
    {
        // Allow only selecting bosses
        $boss = Collection::where(
            function ($query) use ($bossName) {
                $query->where('category_id', '=', 2)
                    ->orWhere('category_id', '=', 3);
            })->where('name', $bossName)->firstOrFail();

        return new CollectionResource($boss->model::where('account_id', Helper::getAccountIdFromUsername($accountUsername))->first());
    }
}
