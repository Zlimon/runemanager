<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvatarRequest;
use App\Models\Account;
use App\Services\Accounts\StoreAvatar;
use Illuminate\Http\JsonResponse;

class AvatarController extends Controller
{
    /**
     * Receive a player 3D model exported by the RuneLite plugin and hand it to
     * StoreAvatar, then tell an open profile to reload the model.
     *
     * The Account is resolved by the plugin.account middleware.
     */
    public function update(StoreAvatarRequest $request, StoreAvatar $storeAvatar): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $storeAvatar->store(
            $account,
            $request->file('model'),
            $request->file('material'),
            $request->file('npc_model'),
            $request->file('npc_material'),
        );

        broadcast(new AccountDataUpdated($account, 'avatar'));

        return response()->json([
            'data' => [
                'avatar_uploaded_at' => $account->avatar_uploaded_at->toIso8601String(),
            ],
        ]);
    }
}
