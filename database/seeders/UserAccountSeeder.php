<?php

namespace Database\Seeders;

use App\Helpers\Helper;
use App\User;
use Illuminate\Database\Seeder;

class UserAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accounts = ["Jern Zlimon", "IronicOcelot", "_most_likely_not_an_account_", "Mmorpg", "Zezima", "Settled", "Hey Jase"];

        foreach ($accounts as $account) {
            $user = User::inRandomOrder()->pluck('id')->first();

            Helper::createOrUpdateAccount($account, Helper::listAccountTypes()[rand(0, 3)], $user);
        }
    }
}
