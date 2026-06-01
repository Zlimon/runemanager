<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Quest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestController extends Controller
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
            'quests' => $account->quests,
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
     *
     * NB: SPEC §5.2 calls for quests in PostgreSQL; the current Quest model lives in
     * MongoDB. Keeping the existing model for now — moving it is a separate wedge.
     */
    public function update(Request $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $request->validate([
            'quests' => ['required', 'array'],
            'quests.*' => ['required', 'array'],
            'quests.*.0' => ['required', 'string'],  // quest name
            'quests.*.1' => ['required', 'integer'], // status (0 = not started, 1 = in progress, 2 = finished)
        ]);

        $quests = Quest::where('account_id', $account->id)->first()
            ?? (new Quest)->forceFill(['account_id' => $account->id]);

        $quests->quests = $request->input('quests');
        $quests->save();

        return response()->json(['data' => $quests]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
