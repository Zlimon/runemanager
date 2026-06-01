<?php

namespace App\Services\Accounts;

use App\Models\Account;
use App\Models\UsernameHistory;
use Illuminate\Support\Facades\DB;

class RecordUsernameChange
{
    public function record(Account $account, string $newUsername): ?UsernameHistory
    {
        if ($account->username === $newUsername) {
            return null;
        }

        return DB::transaction(function () use ($account, $newUsername) {
            $history = UsernameHistory::create([
                'account_id' => $account->id,
                'old_username' => $account->username,
                'new_username' => $newUsername,
                'detected_at' => now(),
            ]);

            $account->username = $newUsername;
            $account->save();

            return $history;
        });
    }
}
