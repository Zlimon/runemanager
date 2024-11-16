<?php

namespace App\Actions\Account;

use App\Models\Account;
use App\Models\Equipment;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateAccountEquipment
{
    /**
     * @throws \Exception
     */
    public function createOrUpdateAccountEquipment(Account $account, array $equipment): Equipment
    {
        DB::beginTransaction();

        try {
            $accountEquipment = $account->equipment()->first();

            if (! $accountEquipment) {
                $accountEquipment = new Equipment;
            }

            $accountEquipment->account_id = $account->id;

            $mergedAttributes = array_merge($accountEquipment->toArray(), $equipment);
            $accountEquipment->fill($mergedAttributes);

            $accountEquipment->save();
        } catch (\Exception $e) {
            DB::rollback();

            throw new \Exception(sprintf("Could not create or update account equipment for '%s'. Message: %s", $account->username, $e->getMessage()));
        }

        DB::commit();

        return $accountEquipment;
    }
}
