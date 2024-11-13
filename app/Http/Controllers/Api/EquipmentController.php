<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EquipmentResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
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
     * @param  Account  $account
     * @return JsonResponse
     */
    public function show(Account $account): JsonResponse
    {
        return response()->json([
            'equipment' => $account->equipment ? new EquipmentResource($account->equipment) : null,
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
     * @param  Request  $request
     * @param  Account  $account
     * @return JsonResponse
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $request->validate([
            'equipment' => 'required|array',
            'equipment.*' => 'required|array',
            'equipment.*.*' => 'required|integer',
        ]);

        try {
            $head = $request['equipment'][0][0] !== -1 ? $request['equipment'][0][0] : null;
            $cape = $request['equipment'][1][0] !== -1 ? $request['equipment'][1][0] : null;
            $neck = $request['equipment'][2][0] !== -1 ? $request['equipment'][2][0] : null;
            $ammo = $request['equipment'][13][0] !== -1 ? $request['equipment'][13][0] : null;
            $weapon = $request['equipment'][3][0] !== -1 ? $request['equipment'][3][0] : null;
            $body = $request['equipment'][4][0] !== -1 ? $request['equipment'][4][0] : null;
            $shield = $request['equipment'][5][0] !== -1 ? $request['equipment'][5][0] : null;
            $legs = $request['equipment'][7][0] !== -1 ? $request['equipment'][7][0] : null;
            $hands = $request['equipment'][9][0] !== -1 ? $request['equipment'][9][0] : null;
            $feet = $request['equipment'][10][0] !== -1 ? $request['equipment'][10][0] : null;
            $ring = $request['equipment'][12][0] !== -1 ? $request['equipment'][12][0] : null;

            $account->equipment->head = $head;
            $account->equipment->cape = $cape;
            $account->equipment->neck = $neck;
            $account->equipment->ammo = $ammo;
            $account->equipment->weapon = $weapon;
            $account->equipment->body = $body;
            $account->equipment->shield = $shield;
            $account->equipment->legs = $legs;
            $account->equipment->hands = $hands;
            $account->equipment->feet = $feet;
            $account->equipment->ring = $ring;

            $account->equipment->save();

            return response()->json([
                'data' => $request,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the account. Message: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
