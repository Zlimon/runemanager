<?php

namespace App\Console\Commands;

use App\Jobs\SyncWomPlayerJob;
use App\Models\Account;
use App\Support\Instance;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('wom:sync {username? : Specific OSRS username to sync; if omitted, syncs every account}')]
#[Description('Queue a Wise Old Man refresh (EHP/EHB, progress history) for each account.')]
class WomSync extends Command
{
    public function handle(): int
    {
        if (! Instance::womEnabled()) {
            $this->info('The Wise Old Man integration is disabled.');

            return self::SUCCESS;
        }

        $username = $this->argument('username');

        $accounts = $username
            ? Account::whereUsername($username)->get(['id', 'username'])
            : Account::all(['id', 'username']);

        if ($username && $accounts->isEmpty()) {
            $this->error(sprintf('No account found with username "%s".', $username));

            return self::FAILURE;
        }

        // Queued rather than run inline: the jobs share WOM's per-minute
        // budget, so a large roster would otherwise hold the scheduler for
        // most of an hour.
        foreach ($accounts as $account) {
            SyncWomPlayerJob::dispatch($account->id);
            $this->line(sprintf('  → %s', $account->username));
        }

        $this->info(sprintf('Queued %d.', $accounts->count()));

        return self::SUCCESS;
    }
}
