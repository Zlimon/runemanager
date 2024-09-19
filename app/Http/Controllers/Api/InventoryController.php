<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryResource;
use App\Models\Account;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
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
            'inventory' => (new InventoryResource(Inventory::where('account_id', $account->id)->first()))->resolve(),
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
     *
     * @param Request $request
     * @param Account $account
     * @return JsonResponse
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $request->validate([
            'inventory' => ['required', 'array', 'max:28'],
            'inventory.*' => ['required', 'array'],
            'inventory.*.0' => ['required', 'integer'],
            'inventory.*.1' => ['required', 'integer'],
        ]);

        // This does not work for MongoDB
    //    $account->inventory()->updateOrCreate([
    //        'account_id' => $account->id
    //    ], [
    //        'inventory' => $request->input('inventory')
    //    ]);

        $inventory = Inventory::where('account_id', $account->id)->first();

        if (!$inventory) {
            $inventory = new Inventory();
            $inventory->account_id = $account->id;
        }

        $inventory->inventory = $request['inventory'];

        $inventory->save();

        return response()->json([
            'data' => $account->inventory,
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
