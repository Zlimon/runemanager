<?php

namespace RuneManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates registered account data (level, xp, skill)';

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
        $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

        $getMembers = DB::table('accounts')->select('id', 'username', 'xp')->get();
        
        foreach ($getMembers as $member) {
            $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.$member->username;

            /* Get the $playerDataUrl file content. */
            $getPlayerData = file_get_contents($playerDataUrl);

            /* Fetch the content from $playerDataUrl. */
            $playerStats = explode("\n", $getPlayerData);

            /* Convert the CSV file of player stats into an array */
            $playerData = array();
            foreach ($playerStats as $playerStat) {
                $playerData[] = str_getcsv($playerStat);
            }

            if ($member->xp != $playerData[0][2]) {
                print_r("Detected ".$member->username." in database with outdated data! Updating...\r\n");

                $updatePlayerData = DB::table('accounts')->where('id', $member->id)
                    ->update(
                        ['rank' => $playerData[0][0], 'level' => $playerData[0][1], 'xp' => $playerData[0][2], 'updated_at' => Carbon::now()]
                    );

                for ($skillCounter = 0; $skillCounter < count($skills); $skillCounter++) {
                    $updatePlayerSkill = DB::table($skills[$skillCounter])->where('user_id', $member->id)
                        ->update(
                            ['rank' => $playerData[$skillCounter+1][0], 'level' => $playerData[$skillCounter+1][1], 'xp' => $playerData[$skillCounter+1][2], 'updated_at' => Carbon::now()]
                        );
                }

                print_r("Updated ".$member->username."!\r\n");
            } else {
                print_r("No outdated data for ". $member->username."! Not updating...\r\n");
            }
        }
    }
}
