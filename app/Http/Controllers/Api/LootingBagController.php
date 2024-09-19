<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LootingBagResource;
use App\Models\Account;
use App\Models\LootingBag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LootingBagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Account $account
     * @return JsonResponse
     */
    public function show(Account $account): JsonResponse
    {
        return response()->json([
            'looting_bag' => (new LootingBagResource(LootingBag::where('account_id', $account->id)->first()))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $request->validate([
            'looting_bag' => ['required', 'array', 'max:28'],
            'looting_bag.*' => ['required', 'array'],
            'looting_bag.*.0' => ['required', 'integer'],
            'looting_bag.*.1' => ['required', 'integer'],
        ]);

        // This does not work for MongoDB
    //    $account->lootingBag()->updateOrCreate([
    //        'account_id' => $account->id
    //    ], [
    //        'lootingBag' => $request->input('looting_bag')
    //    ]);

        $lootingBag = LootingBag::where('account_id', $account->id)->first();

        if (!$lootingBag) {
            $lootingBag = new LootingBag();
            $lootingBag->account_id = $account->id;
        }

        $lootingBag->lootingBag = $request['looting_bag'];

        $lootingBag->save();

        return response()->json([
            'data' => $account->lootingBag,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
