<?php

namespace App\Http\Controllers\Api;

use App\Bank;
use App\Group;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Display the group bank resource.
     *
     * @param Group $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function bank(Group $group)
    {
        $accountBanks = $group::with('account.bank')->first();

        $banks = [];
        foreach ($accountBanks->account as $accountId => $account) {
            if ($account->bank->display === 1) {
                foreach ($account->bank->data as $bankItem) {
                    $banks['account-bank-' . $accountId][$bankItem['id']] = $bankItem;
                }
            }
        }

        $priceType = "gePrice"; // TODO gePrice / haPrice -> Admin panel

        $mergedBanks = [];
        for ($i = 0; $i < count($banks); $i++) {
            $accountBankId = 'account-bank-' . $i;

            if (isset($banks[$accountBankId]) && is_array($banks[$accountBankId])) {
                foreach ($banks[$accountBankId] as $itemId => $bankItem) {
                    if (!isset($mergedBanks[$itemId]['quantity'])) {
                        $mergedBanks[$itemId]['id'] = $bankItem['id'];
                        $mergedBanks[$itemId]['quantity'] = 0;
                        $mergedBanks[$itemId]['name'] = $bankItem['name'];
                        $mergedBanks[$itemId][$priceType] = $bankItem[$priceType];
                    }

                    $mergedBanks[$itemId]['quantity'] = $mergedBanks[$itemId]['quantity'] + $bankItem['quantity'];
                }
            }
        }

        $mergedBanks = array_values($mergedBanks);

        $totalBankValue = 0;
        foreach ($mergedBanks as $bankItem) {
            $totalBankValue += $bankItem[$priceType] * $bankItem["quantity"];
        }

        return response()->json([
            "group_id" => $group->id,
            "data" => $mergedBanks,
            "total" => $totalBankValue,
            "display" => 1, // TODO Group config
        ], 200);
    }
}
