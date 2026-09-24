<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBankRequest;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;

class BankController extends Controller
{
    /**
     * Snapshot upsert from the RuneLite plugin. Account resolved by plugin.account middleware.
     */
    public function update(UpdateBankRequest $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $bank = Bank::where('account_id', $account->id)->first()
            ?? (new Bank)->forceFill(['account_id' => $account->id]);

        $bank->bank = $request->validated('bank');
        $bank->save();

        broadcast(new AccountDataUpdated($account, 'bank'));

        return response()->json(['data' => $bank]);
    }
}
