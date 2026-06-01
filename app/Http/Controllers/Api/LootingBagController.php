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
     */
    public function show(Account $account): JsonResponse
    {
        return response()->json([
            'looting_bag' => $account->lootingBag ? new LootingBagResource($account->lootingBag) : null,
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
     * Snapshot upsert from the RuneLite plugin. Account resolved by plugin.account middleware.
     */
    public function update(Request $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $request->validate([
            'looting_bag' => ['required', 'array', 'max:28'],
            'looting_bag.*' => ['required', 'array', 'max:2'],
            'looting_bag.*.0' => ['required', 'integer'],
            'looting_bag.*.1' => ['required', 'integer'],
        ]);

        $bag = LootingBag::where('account_id', $account->id)->first()
            ?? (new LootingBag)->forceFill(['account_id' => $account->id]);

        // Store under snake_case to match the model's $fillable and the show()/Resource.
        $bag->looting_bag = $request->input('looting_bag');
        $bag->save();

        return response()->json(['data' => $bag]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
