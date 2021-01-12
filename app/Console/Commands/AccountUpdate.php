<?php

namespace App\Console\Commands;

use App\Account;
use App\Collection;
use App\Helpers\Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            if ($account->online === 0) {
                $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=' . str_replace(' ',
                        '%20', $account->username);

                /* Get the $playerDataUrl file content. */
                $playerData = Helper::getPlayerData($playerDataUrl);

                if ($playerData) {
                    if ($account->xp != $playerData[0][2]) {
                        $this->info(sprintf("Found outdated data for %s!", $account->username));

                        $account->rank = $playerData[0][0];
                        $account->level = $playerData[0][1];
                        $account->xp = $playerData[0][2];

                        $account->update();

                        $skills = Helper::listSkills();

                        for ($i = 0; $i < count($skills); $i++) {
                            DB::table($skills[$i])
                                ->where('account_id', $account->id)
                                ->update([
                                    'rank' => ($playerData[$i + 1][0] >= 1 ? $playerData[$i + 1][0] : 0),
                                    'level' => $playerData[$i + 1][1],
                                    'xp' => ($playerData[$i + 1][2] >= 0 ? $playerData[$i + 1][2] : 0)
                                ]);
                        }

                        $clueScrollAmount = count(Helper::listClueScrollTiers());

                        $bosses = Helper::listBosses();

                        array_splice($bosses, 13, 1);

                        $bossIndex = 0;

                        $dksKillCount = 0;

                        for ($i = (count($skills) + $clueScrollAmount + 5); $i < (count($skills) + $clueScrollAmount + 5 + count($bosses)); $i++) {
                            $collection = Collection::where('name', $bosses[$bossIndex])->firstOrFail();

                            $collectionLoot = $collection->model::where('account_id', $account->id)->firstOrFail();

                            $collectionLoot->account_id = $account->id;
                            $collectionLoot->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
                            $collectionLoot->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

                            if (in_array($bosses[$bossIndex],
                                ['dagannoth prime', 'dagannoth rex', 'dagannoth supreme'], true)) {
                                $dksKillCount += ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
                            }

                            $collectionLoot->update();

                            $bossIndex++;
                        }

                        /**
                         * Since there are no official total kill count hiscore for
                         * DKS' and we are going to retrieve loot for them from the
                         * collection log, we have to manually create a table.
                         * This might also happen with other bosses in the future
                         * that share collection log entry, but have separate hiscores.
                         */
                        $dks = \App\Boss\DagannothKings::where('account_id', $account->id)->firstOrFail();

                        $dks->kill_count = $dksKillCount;

                        $dks->update();

                        $this->info(sprintf("Updated %s!", $account->username));
                    } else {
                        $this->info(sprintf("No outdated data for %s! Not updating", $account->username));
                    }
                } else {
                    $this->info(sprintf("Could not fetch player data for %s from hiscores! Not updating", $account->username));
                }
            } else {
                $this->info(sprintf("%s is logged in to the game! Not updating", $account->username));
            }
        }
    }
}
