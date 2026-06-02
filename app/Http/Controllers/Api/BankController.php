<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankController extends Controller
{
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
}
