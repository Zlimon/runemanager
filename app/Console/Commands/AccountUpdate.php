<?php

namespace App\Console\Commands;

use App\Account;
use App\Helpers\Helper;
use App\Http\Controllers\Api\AccountController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccountUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:update
                            {--username= : Update only this account}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches up-to-date account data from Old School RuneScape hiscores';

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
        $accounts = [];

        $username = $this->option('username');
        if (!is_null($username)) {
            $account = Account::whereUsername($username)->first();

            if (!$account) {
                // TODO this will cause trouble if two or more accounts has the "same" name, but differentiate them using _ or -
                // TLDR Command argument needs to support spaces
                $username = str_replace(['_', '-'], ' ', $username);
                $account = Account::whereUsername($username)->first();

                if (!$account) {
                    $this->info(sprintf('Could not find any existing account with username "%s".', $username));

                    return 1;
                }
            }

            $accounts[] = $account;
        } else {
            $accounts = Account::get();
        }

        foreach ($accounts as $account) {
            if ($account->online !== 0) {
                $this->info(sprintf('"%s" is logged in to the game! Not updating.', $account->username));

                continue;
            }

            DB::beginTransaction();

            $account = Helper::createOrUpdateAccount($account->username, $account->account_type, $account->user_id, true);

            if ($account instanceof Account) {
                $this->info(sprintf('Successfully updated account "%s".', $account->username));
            } else {
                $this->info(sprintf($account));

                continue;
            }

            DB::commit();
        }

        return 0;
    }
}
