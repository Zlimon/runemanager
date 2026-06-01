<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\Hiscores\HiscoresSync as HiscoresSyncService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('hiscores:sync {username : OSRS username (matches accounts.username)}')]
#[Description('Fetch the latest hiscores for an account from the official OSRS hiscores API and upsert the JSONB row.')]
class HiscoresSync extends Command
{
    public function handle(HiscoresSyncService $sync): int
    {
        $username = $this->argument('username');

        $account = Account::whereUsername($username)->first();

        if (! $account) {
            $this->error(sprintf('No account found with username "%s".', $username));

            return self::FAILURE;
        }

        try {
            $hiscore = $sync->syncForAccount($account);
        } catch (GuzzleException $e) {
            $this->error(sprintf('Hiscores fetch failed: %s', $e->getMessage()));

            return self::FAILURE;
        }

        $skills = count($hiscore->entries['skills'] ?? []);
        $activities = count($hiscore->entries['activities'] ?? []);

        $this->info(sprintf(
            'Synced %s — %d skills, %d activities (fetched %s)',
            $account->username,
            $skills,
            $activities,
            $hiscore->fetched_at->toDateTimeString(),
        ));

        return self::SUCCESS;
    }
}
