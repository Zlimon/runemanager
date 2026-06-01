<?php

namespace App\Console\Commands;

use App\Services\Accounts\DetectUsernameChange;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('name-changes:detect {--pages=2 : How many WOM pages of 50 to scan per run}')]
#[Description('Poll the Wise Old Man approved-name-changes feed and apply any matching one of our tracked accounts.')]
class NameChangesDetect extends Command
{
    public function handle(DetectUsernameChange $detector): int
    {
        $pages = max(1, (int) $this->option('pages'));

        try {
            $summary = $detector->run(pages: $pages);
        } catch (GuzzleException $e) {
            $this->error(sprintf('WOM fetch failed: %s', $e->getMessage()));

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Checked %d WOM entries, matched %d accounts, applied %d renames.',
            $summary['checked'],
            $summary['matched'],
            $summary['applied'],
        ));

        return self::SUCCESS;
    }
}
