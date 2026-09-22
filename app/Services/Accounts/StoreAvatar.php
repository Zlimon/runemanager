<?php

namespace App\Services\Accounts;

use App\Models\Account;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Store a player 3D model exported by the RuneLite plugin on the public disk,
 * where the Account/Show Three.js viewer loads it.
 *
 * The OBJ + MTL pair is normalised to a fixed avatar.obj / avatar.mtl name
 * inside a per-account directory. The viewer assigns materials explicitly, so
 * the OBJ's `mtllib` line is irrelevant; only the material *names* need to
 * match, which they do for a given exported pair. The opponent model rides
 * along as avatar_npc.obj / avatar_npc.mtl only while the player is fighting.
 */
class StoreAvatar
{
    /**
     * Store the upload and stamp the account's `avatar_uploaded_at`. A file
     * absent from this upload is deleted, so the viewer never pairs a new model
     * with old colours or keeps showing an NPC once the player stops fighting.
     */
    public function store(
        Account $account,
        UploadedFile $model,
        ?UploadedFile $material,
        ?UploadedFile $npcModel,
        ?UploadedFile $npcMaterial,
    ): void {
        $directory = "avatars/{$account->id}";

        $model->storeAs($directory, 'avatar.obj', 'public');
        $this->storeOrDelete($material, $directory, 'avatar.mtl');

        if ($npcModel !== null) {
            $npcModel->storeAs($directory, 'avatar_npc.obj', 'public');
            $this->storeOrDelete($npcMaterial, $directory, 'avatar_npc.mtl');
        } else {
            Storage::disk('public')->delete(["{$directory}/avatar_npc.obj", "{$directory}/avatar_npc.mtl"]);
        }

        $account->forceFill(['avatar_uploaded_at' => now()])->save();
    }

    private function storeOrDelete(?UploadedFile $file, string $directory, string $name): void
    {
        if ($file !== null) {
            $file->storeAs($directory, $name, 'public');
        } else {
            Storage::disk('public')->delete("{$directory}/{$name}");
        }
    }
}
