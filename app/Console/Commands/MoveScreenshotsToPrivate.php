<?php

namespace App\Console\Commands;

use App\Services\Screenshots\MoveScreenshotsToPrivateDisk;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;

class MoveScreenshotsToPrivate extends Command
{
    protected $signature = 'screenshots:move-private
        {--prune-missing : Clear the screenshot of legacy rows whose public file is gone, instead of only reporting them}';

    protected $description = 'Move feed screenshots still on the public disk to private screenshot storage. Safe to run on every deploy.';

    /**
     * The migration moves every screenshot once; this runs again after a
     * deploy's activation to catch those the previous release stored publicly
     * while the deploy was under way. Fails loudly, keeping the originals, when
     * a file cannot be copied. A missing file is only reported unless
     * `--prune-missing` is given; the deploy hook never passes it.
     */
    public function handle(MoveScreenshotsToPrivateDisk $move): int
    {
        try {
            $result = $move->run($this->option('prune-missing'));
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return CommandAlias::FAILURE;
        }

        $this->info(sprintf('Moved %d screenshots; cleared %d whose file was missing.', $result['moved'], $result['pruned']));

        if ($result['missing'] !== []) {
            $this->warn(sprintf(
                '%d feed events point at a public screenshot that is missing (kept; rerun with --prune-missing to clear): %s',
                count($result['missing']),
                implode(', ', $result['missing']),
            ));
        }

        foreach ($result['unreferenced'] as $path) {
            $this->warn("Public file no feed event points at (left in place): {$path}");
        }

        return CommandAlias::SUCCESS;
    }
}
