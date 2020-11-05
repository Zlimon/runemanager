<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Helpers\Helper;

use App\Account;
use App\Collection;

class AccountUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches up-to-date data (account level, xp and skill) from Old School RuneScape hiscores';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accounts = Account::get();
        
        foreach ($accounts as $account) {
            $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.str_replace(' ', '%20', $account->username);

            /* Get the $playerDataUrl file content. */
            $getPlayerData = file_get_contents($playerDataUrl);

            /* Fetch the content from $playerDataUrl. */
            $playerStats = explode("\n", $getPlayerData);

            /* Convert the CSV file of player stats into an array */
            $playerData = [];
            foreach ($playerStats as $playerStat) {
                $playerData[] = str_getcsv($playerStat);
            }

            if ($playerData[0][0]) {
                if ($account->xp != $playerData[0][2]) {
                    $this->info(sprintf("Detected %s in database with outdated data! Updating...", $account->username));

                    $account->rank = $playerData[0][0];
                    $account->level = $playerData[0][1];
                    $account->xp = $playerData[0][2];

                    $account->update();

                    $skills = Helper::listSkills();

                    for ($i = 0; $i < count($skills); $i++) {
                        DB::table($skills[$i])
                            ->where('account_id', $account->id)
                            ->update(['rank' => ($playerData[$i+1][0] >= 1 ? $playerData[$i+1][0] : 0), 'level' => $playerData[$i+1][1], 'xp' => ($playerData[$i+1][2] >= 0 ? $playerData[$i+1][2] : 0)]);
                    }

                    $clueScrollAmount = count(Helper::listClueScrollTiers());

                    $bosses = Helper::listBosses();

                    array_splice($bosses, 13, 1);

                    $bossCounter = 0;

                    $dksKillCount = 0;

                    for ($i = (count($skills) + $clueScrollAmount + 4); $i < (count($skills) + $clueScrollAmount + 4 + count($bosses)); $i++) {
                        $collection = Collection::findByName($bosses[$bossCounter]);

                        $collection = $collection->model::where('account_id', $account->id)->first();

                        $collection->kill_count = ($playerData[$i+1][1] >= 0 ? $playerData[$i+1][1] : 0);
                        $collection->rank = ($playerData[$i+1][0] >= 0 ? $playerData[$i+1][0] : 0);

                        $collection->update();

                        if (in_array($bosses[$bossCounter], ['dagannoth prime', 'dagannoth rex', 'dagannoth supreme'], true)) {
                            $dksKillCount += ($playerData[$i+1][1] >= 0 ? $playerData[$i+1][1] : 0);
                        }

                        $bossCounter++;
                    }

                    /**
                     * Since there are no official total kill count hiscore for
                     * DKS' and we are going to retrieve loot for them from the
                     * collection log, we have to manually create a table.
                     * This might also happen with other bosses in the future.
                     */
                    $dks = \App\Boss\DagannothKings::where('account_id', $account->id)->first();;

                    $dks->kill_count = $dksKillCount;

                    $dks->update();

                    $this->info(sprintf("Updated %s!", $account->username));
                } else {
                    $this->info(sprintf("No outdated data for %s! Not updating...", $account->username));
                }
            } else {
                $this->info(sprintf("Could not fetch player data from hiscores"));
            }
        }
    }
}
