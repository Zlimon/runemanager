<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankResource;
use App\Models\Account;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankController extends Controller
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
            'bank' => (new BankResource(Bank::where('account_id', $account->id)->first()))->resolve(),
        ], 200);
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
            'bank' => ['required', 'array', 'max:10'],            // tabs
            'bank.*' => ['required', 'array'],                    // items in a tab
            'bank.*.*' => ['required', 'array', 'size:2'],        // [itemId, quantity]
            'bank.*.*.*' => ['required', 'integer'],
        ]);

        $bank = Bank::where('account_id', $account->id)->first()
            ?? (new Bank)->forceFill(['account_id' => $account->id]);

        $bank->bank = $request->input('bank');
        $bank->save();

        return response()->json(['data' => $bank]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
