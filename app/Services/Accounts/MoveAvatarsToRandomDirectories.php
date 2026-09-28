<?php

namespace App\Services\Accounts;

use App\Models\Account;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Move every avatar from its guessable `avatars/{account id}` folder into a
 * random one ({@see StoreAvatar::newDirectory()}), recording it on the account.
 *
 * Each file is copied and checked before the account points at the new folder,
 * and the old folder is only removed after that, so a failure leaves the
 * account on its old, still complete, avatar. Accounts already on a random
 * folder are skipped, which makes it safe to run again.
 */
class MoveAvatarsToRandomDirectories
{
    /**
     * @return array{moved: int, missing: int}
     */
    public function run(): array
    {
        $disk = Storage::disk('public');
        $moved = 0;
        $missing = 0;

        Account::query()
            ->whereNotNull('avatar_uploaded_at')
            ->whereNull('avatar_directory')
            ->chunkById(200, function ($accounts) use ($disk, &$moved, &$missing): void {
                foreach ($accounts as $account) {
                    $legacy = StoreAvatar::legacyDirectory($account);
                    $files = $disk->files($legacy);

                    if ($files === []) {
                        // Nothing on disk to show: stop claiming an avatar exists.
                        $account->forceFill(['avatar_uploaded_at' => null])->save();
                        $missing++;

                        continue;
                    }

                    $directory = StoreAvatar::newDirectory();

                    foreach ($files as $file) {
                        $target = $directory.'/'.basename($file);

                        if (! $disk->copy($file, $target) || $disk->size($target) !== $disk->size($file)) {
                            $disk->deleteDirectory($directory);

                            throw new RuntimeException("Could not copy avatar file {$file}; account {$account->id} keeps its old avatar.");
                        }
                    }

                    $account->forceFill(['avatar_directory' => $directory])->save();
                    $disk->deleteDirectory($legacy);
                    $moved++;
                }
            });

        return ['moved' => $moved, 'missing' => $missing];
    }
}
