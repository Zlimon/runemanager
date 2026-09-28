<?php

namespace App\Services\Accounts;

use App\Models\Account;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Store a player 3D model exported by the RuneLite plugin on the public disk,
 * where the Account/Show Three.js viewer loads it.
 *
 * Every upload goes into a fresh UUID folder (`avatars/{uuid}`), recorded as
 * the account's `avatar_directory`, and the previous folder is deleted once the
 * account points at the new one. The folder name is the only thing standing
 * between the public disk and anyone guessing URLs, so it is never derived
 * from the account; a new one per upload also retires old links and busts
 * browser caches without a query string.
 *
 * Inside it the OBJ + MTL pair is normalised to avatar.obj / avatar.mtl. The
 * viewer assigns materials explicitly, so the OBJ's `mtllib` line is
 * irrelevant; only the material *names* need to match, which they do for a
 * given exported pair. The opponent model rides along as avatar_npc.obj /
 * avatar_npc.mtl only while the player is fighting.
 */
class StoreAvatar
{
    public const ROOT = 'avatars';

    /**
     * Store the upload and stamp the account's `avatar_uploaded_at`. A file
     * absent from this upload simply isn't in the new folder, so the viewer
     * never pairs a new model with old colours or keeps showing an NPC once
     * the player stops fighting.
     */
    public function store(
        Account $account,
        UploadedFile $model,
        ?UploadedFile $material,
        ?UploadedFile $npcModel,
        ?UploadedFile $npcMaterial,
    ): void {
        $previous = $account->avatar_directory;
        $directory = self::newDirectory();

        $model->storeAs($directory, 'avatar.obj', 'public');
        $material?->storeAs($directory, 'avatar.mtl', 'public');

        if ($npcModel !== null) {
            $npcModel->storeAs($directory, 'avatar_npc.obj', 'public');
            $npcMaterial?->storeAs($directory, 'avatar_npc.mtl', 'public');
        }

        $account->forceFill([
            'avatar_directory' => $directory,
            'avatar_uploaded_at' => now(),
        ])->save();

        $disk = Storage::disk('public');

        if ($previous !== null) {
            $disk->deleteDirectory($previous);
        }

        // Written by a release from before the random folders (e.g. during a
        // deploy); it would otherwise stay reachable under the account id.
        $disk->deleteDirectory(self::legacyDirectory($account));
    }

    /** Remove the account's avatar files (roster removal). */
    public function delete(Account $account): void
    {
        $disk = Storage::disk('public');

        if ($account->avatar_directory !== null) {
            $disk->deleteDirectory($account->avatar_directory);
        }

        $disk->deleteDirectory(self::legacyDirectory($account));
    }

    /** Remove every avatar on the instance (instance reset). */
    public function deleteAll(): void
    {
        Storage::disk('public')->deleteDirectory(self::ROOT);
    }

    public static function newDirectory(): string
    {
        return self::ROOT.'/'.Str::uuid()->toString();
    }

    /** Where avatars lived before they had random folders. */
    public static function legacyDirectory(Account $account): string
    {
        return self::ROOT.'/'.$account->id;
    }
}
