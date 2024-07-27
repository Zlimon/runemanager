<?php

namespace Database\Seeders;

use App\Actions\Account\CreateOrUpdateAccount;
use App\Enums\AccountTypesEnum;
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

        $createOrUpdateAccount = new CreateOrUpdateAccount();

        $createOrUpdateAccount->createOrUpdateAccount('Habski', User::whereName('Zlimon')->first(), AccountTypesEnum::IRONMAN);

        foreach ($accounts as $account) {
            $user = User::inRandomOrder()->pluck('id')->first();

//            \App\Enums\AccountTypesEnum::returnAllAccountTypes()[rand(0, 3)]
            $createOrUpdateAccount->createOrUpdateAccount($account, $user);
        }
    }
}
