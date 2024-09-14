<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function show(string $id)
    {
        //
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
        'bank' => 'required|array',
        'bank.*' => 'required|array',
        'bank.*.*' => 'required|array',
        'bank.*.*.*' => 'required|integer',
    ]);

    // This does not work for MongoDB
//    $account->bank()->updateOrCreate([
//        'account_id' => $account->id
//    ], [
//        'bank' => $request->input('bank')
//    ]);

    $bank = Bank::where('account_id', $account->id)->first();

    if (!$bank) {
        $bank = new Bank();
        $bank->account_id = $account->id;
    }

    $bank->bank = $request['bank'];

    $bank->save();

    return response()->json([
        'data' => $account->bank,
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
