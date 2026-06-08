<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvatarRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    /**
     * Receive a player 3D model exported by the RuneLite plugin and stored
     * under the public disk so the Account/Show Three.js viewer can load it.
     *
     * The OBJ + MTL pair is normalised to a fixed avatar.obj / avatar.mtl name
     * inside a per-account directory. The viewer assigns materials explicitly,
     * so the OBJ's `mtllib` line is irrelevant — only the material *names* need
     * to match, which they do for a given exported pair.
     *
     * The Account is resolved by the plugin.account middleware.
     */
    public function update(StoreAvatarRequest $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $directory = "avatars/{$account->id}";

        $request->file('model')->storeAs($directory, 'avatar.obj', 'public');

        if ($request->hasFile('material')) {
            $request->file('material')->storeAs($directory, 'avatar.mtl', 'public');
        } else {
            // Drop a stale material from a previous upload so the viewer doesn't
            // pair the new model with old colours.
            Storage::disk('public')->delete("{$directory}/avatar.mtl");
        }

        $account->forceFill(['avatar_uploaded_at' => now()])->save();

        broadcast(new AccountDataUpdated($account, 'avatar'));

        return response()->json([
            'data' => [
                'avatar_uploaded_at' => $account->avatar_uploaded_at->toIso8601String(),
            ],
        ]);
    }
}
