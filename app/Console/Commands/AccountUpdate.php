<?php

namespace App\Console\Commands;

use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

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
    protected $description = 'Fetches up-to-date account data from Old School RuneScape hiscores, and creates or updates the account in the database.';

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle(): int
    {
        $username = $this->option('username');

        if ($username) {
            $account = Account::whereUsername($username)->first();

            if (!$account) {
                $userName = $this->choice("Choose user to assign account to", User::orderBy('id')->pluck('name')->all());

                if (!$userName) {
                    $this->error("No user selected.");

                    return CommandAlias::FAILURE;
                }

                $user = User::whereName($userName)->first();

                $account = AccountHelper::createOrUpdateAccount($username, $user);

                try {
                    $this->info(sprintf("Successfully created account '%s'.", $account->username));
                } catch (\Exception $e) {
                    $this->error(sprintf("Could not create account '%s'. Message: %s", $account->username, $e->getMessage()));
                }

                return CommandAlias::SUCCESS;
            }

            $accounts[] = $account;
        } else {
            $account = $this->choice("Choose account", array_merge(Account::orderBy('id')->pluck('username')->all(), ['all']));

            if ($account == 'all') {
                $accounts = Account::all();
            } else {
                $accounts[] = Account::whereUsername($account)->first();
            }
        }

//        if (!is_null($username)) {
//            $account = Account::whereUsername($username)->first();
//
//            if (!$account) {
//                // TODO this will cause trouble if two or more accounts has the "same" name, but differentiate them using _ or -
//                // TLDR Command argument needs to support spaces
//                $username = str_replace(['_', '-'], ' ', $username);
//                $account = Account::whereUsername($username)->first();
//
//                if (!$account) {
//                    $this->info(sprintf('Could not find any existing account with username "%s".', $username));
//
//                    return 1;
//                }
//            }
//
//            $accounts[] = $account;
//        } else {
//            $accounts = Account::all();
//        }

        foreach ($accounts as $account) {
            if ($account->online !== 0) {
                $this->warn(sprintf("'%s' is logged in to the game! Not updating.", $account->username));

                continue;
            }

            try {
                AccountHelper::createOrUpdateAccount($account->username, $account->user()->first(), constant(sprintf("App\Enums\AccountTypesEnum::%s", strtoupper($account->account_type))));

                $this->info(sprintf("Successfully updated account '%s'.", $account->username));
            } catch (\Exception $e) {
                $this->warn(sprintf("Could not update account '%s'. Message: %s", $account->username, $e->getMessage()));

                continue;
            }
        }

        return CommandAlias::SUCCESS;
    }
}
