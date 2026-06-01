<?php

namespace App\Actions\Account;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\User;
use App\Services\Hiscores\HiscoresSync;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateAccount
{
    public function __construct(protected HiscoresSync $sync) {}

    /**
     * @throws \Exception
     */
    public function createOrUpdateAccount(string $accountUsername, User $user, AccountTypesEnum $accountType = AccountTypesEnum::NORMAL): Account
    {
        DB::beginTransaction();

        try {
            $account = Account::firstOrNew(['username' => $accountUsername]);
            $account->user_id = $user->id;
            $account->account_type = $accountType;
            $account->username = $accountUsername;
            $account->rank ??= 0;
            $account->level ??= 0;
            $account->xp ??= 0;
            $account->save();
        } catch (\Exception $e) {
            DB::rollback();

            throw new \Exception(sprintf("Could not create or update account '%s'. Message: %s", $accountUsername, $e->getMessage()));
        }

        DB::commit();

        $hiscore = $this->sync->syncForAccount($account);

        $overall = $hiscore->entries['skills']['overall'] ?? null;

        if ($overall) {
            $account->forceFill([
                'rank' => $overall['rank'] ?? 0,
                'level' => $overall['level'] ?? 0,
                'xp' => $overall['xp'] ?? 0,
            ])->save();
        }

        return $account;
    }
}
