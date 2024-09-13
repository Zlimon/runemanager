<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Equipment;
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
     * @param array $equipment
     * @param Account $account
     * @return JsonResponse
     */
    public function update(array $equipment, Account $account): JsonResponse
    {
        try {
            $head =     $equipment[0][0] !== -1 ? $equipment[0][0] : null;
            $cape =     $equipment[1][0] !== -1 ? $equipment[1][0] : null;
            $neck =     $equipment[2][0] !== -1 ? $equipment[2][0] : null;
            $ammo =     $equipment[13][0] !== -1 ? $equipment[13][0] : null;
            $weapon =   $equipment[3][0] !== -1 ? $equipment[3][0] : null;
            $body =     $equipment[4][0] !== -1 ? $equipment[4][0] : null;
            $shield =   $equipment[5][0] !== -1 ? $equipment[5][0] : null;
            $legs =     $equipment[7][0] !== -1 ? $equipment[7][0] : null;
            $hands =    $equipment[9][0] !== -1 ? $equipment[9][0] : null;
            $feet =     $equipment[10][0] !== -1 ? $equipment[10][0] : null;
            $ring =     $equipment[12][0] !== -1 ? $equipment[12][0] : null;

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
                'data' => $equipment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the account. Message: ' . $e->getMessage(),
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
