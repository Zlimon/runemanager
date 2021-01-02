<?php

use App\Account;
use App\Collection;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'uuid' => 'd7d865c5-e37f-4228-a1c1-a5190f0f34cb',
            'name' => 'Simon',
            'email' => 'simon@runemanager.com',
            'password' => bcrypt('runemanager1234'),
            'icon_id' => Helper::randomItemId(true),
        ]);

        $accounts = [
            'Arttu Mikael',
            'GamerButBad',
            'ImNotBobRoss',
            'Darth Morty',
            'Zezima',
            'Settled',
            'Eddapt',
            'SteelmanDave',
            'Mmorpg',
            'Hey Jase',
            'DarthPorg',
            'Slapen',
            'UIM Paperbag',
            'dids',
            'Doctor Nick',
            'Murder',
            'Carlton',
            'Fire 961',
            'Joltik',
            'Shiny Dragon',
            'Lumby',
            'Intrigued',
            'kulitz',
            'TwiztedLore',
            'Vitaliz',
            'Chasadelic',
            'allyallnoobs',
            'Cause Milk',
            'PACKMAN Boi',
            'Meth Mann',
            'AKA boef',
            'hgcdtyr6icto',
            'Rsn-Ihita22',
            'Sr Strong JR',
            'White Web',
            'kyleraw2',
            'Senpai Jayce',
            'Antione',
            'Jhhonnn',
            'Dan Kingdon',
            'BSM',
            'HH Loli',
            'TrumpYoDaddy',
            'LotteryPure',
            'Wargod Benny',
            'heaven_nova',
        ];

        shuffle($accounts);

        factory(App\User::class, 35)->create()->each(function ($u) use ($accounts) {
            $randomId = rand(0, sizeof($accounts) - 1);

            if (App\Account::where('username', $accounts[$randomId])->first()) {
                return null;
            }

            $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=' . str_replace(' ',
                    '%20', $accounts[$randomId]);

            /* Get the $playerDataUrl file content. */
            $playerData = Helper::getPlayerData($playerDataUrl);

            $account = Account::firstOrCreate([
                'user_id' => rand(1, 10),
                'account_type' => Helper::listAccountTypes()[rand(0, 3)],
                'username' => $accounts[$randomId],
                'rank' => $playerData[0][0],
                'level' => $playerData[0][1],
                'xp' => $playerData[0][2]
            ]);

            $skills = Helper::listSkills();

            for ($i = 0; $i < count($skills); $i++) {
                DB::table($skills[$i])->insert([
                    'account_id' => $account->id,
                    'rank' => ($playerData[$i + 1][0] >= 1 ? $playerData[$i + 1][0] : 0),
                    'level' => $playerData[$i + 1][1],
                    'xp' => ($playerData[$i + 1][2] >= 0 ? $playerData[$i + 1][2] : 0),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            $clueScrollAmount = count(Helper::listClueScrollTiers());

            $bosses = Helper::listBosses();

            array_splice($bosses, 13, 1);

            $bossIndex = 0;

            $dksKillCount = 0;

            for ($i = (count($skills) + $clueScrollAmount + 4); $i < (count($skills) + $clueScrollAmount + 4 + count($bosses)); $i++) {
                $collection = Collection::where('name', $bosses[$bossIndex])->firstOrFail();

                $collectionLoot = new $collection->model;

                $collectionLoot->account_id = $account->id;
                $collectionLoot->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
                $collectionLoot->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

                if (in_array($bosses[$bossIndex],
                    ['dagannoth prime', 'dagannoth rex', 'dagannoth supreme'], true)) {
                    $dksKillCount += ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
                }

                $collectionLoot->save();

                $bossIndex++;
            }

            /**
             * Since there are no official total kill count hiscore for
             * DKS' and we are going to retrieve loot for them from the
             * collection log, we have to manually create a table.
             * This might also happen with other bosses in the future
             * that share collection log entry, but have separate hiscores.
             */
            $dks = new \App\Boss\DagannothKings;

            $dks->account_id = $account->id;
            $dks->kill_count = $dksKillCount;

            $dks->save();

            $npcs = Helper::listNpcs();

            foreach ($npcs as $npc) {
                $collection = Collection::findByNameAndCategory($npc, 4);

                $collectionLoot = new $collection->model;

                $collectionLoot->account_id = $account->id;

                $collectionLoot->save();
            }

            print_r('Added ' . $accounts[$randomId]);

            return $account->toArray();
        });
    }
}
