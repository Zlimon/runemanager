<?php

namespace Database\Seeders;

use App\Actions\Account\CreateOrUpdateAccount;
use App\Actions\Account\CreateOrUpdateAccountEquipment;
use App\Enums\AccountTypesEnum;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            'Matilizo',
            'wewantrings',
            'Nex What',
            'Far89',
            'CinnaBawns',
            'Den falahid',
            'Zuk Zillion',
            'Dyla n fan',
            'No Ni',
            'Gronky444',
            'Q R U I Z',
            'R O A D MAX6',
            'a Gemidish55',
            'Ms Effect',
            'Max And Ruby',
            'l0ltit',
            'l 54',
            'WumbleTeed',
            'A N U B I S',
            'ZET0 KA1BA',
            'Jxclusiive22',
            'Dynosauur',
            'Location',
            'Darkrune256',
            'Dharoks ass',
            'Mrs Jesus',
            'Spirit2k15',
            'Kandarin Kin',
            'Didg1t',
            'Dessabrine',
            'spongejoe2',
            'Skullzj102',
            'ante xoxo',
            'zorminis',
            'PokketSand',
            'Fyressence',
            'MezDoe',
            'highlifeng9',
            'Boonana95',
            'Reshy HC',
            'Bigschmoo12',
            'Jo Coolest',
            'Aquat1cz',
            'Varang',
            'LOPrGds',
            'Wigwamjones',
            'Unsponsored',
            'Muswa',
            'GoeieVanHaze',
            'TobeTask',
        ];

        $this->createOrUpdateAccount('Habski', User::whereName('Zlimon')->first(), AccountTypesEnum::IRONMAN);
        $this->createOrUpdateAccount('Marni', User::whereName('Zlimon')->first(), AccountTypesEnum::NORMAL);
        $this->createOrUpdateAccount('Hey Jase', User::whereName('Zlimon')->first(), AccountTypesEnum::NORMAL);

        foreach ($accounts as $account) {
            $user = User::inRandomOrder()->first();

//            \App\Enums\AccountTypesEnum::returnAllAccountTypes()[rand(0, 3)]
            $this->createOrUpdateAccount($account, $user, AccountTypesEnum::NORMAL);
        }
    }

    private function createOrUpdateAccount(string $account, User $user, AccountTypesEnum $accountType): void
    {
        $createOrUpdateAccount = new CreateOrUpdateAccount();
        $createOrUpdateAccountEquipment = new CreateOrUpdateAccountEquipment();

        try {
            $account = $createOrUpdateAccount->createOrUpdateAccount($account, $user, $accountType);

            $equipment = Equipment::factory(1)->make()->first()->toArray();
            $createOrUpdateAccountEquipment->createOrUpdateAccountEquipment($account, $equipment);
        } catch (\Exception $e) {
            $this->command->warn($e->getMessage());
        }
    }
}
