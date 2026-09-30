<?php

namespace App\Console\Commands;

use App\Models\PluginRequest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('plugin-traffic:prune')]
#[Description('Delete plugin request metadata older than the configured retention window.')]
class PrunePluginRequests extends Command
{
    /** How many days of request metadata the traffic monitor keeps. */
    public const RETENTION_DAYS = 14;

    public function handle(): int
    {
        $days = self::RETENTION_DAYS;

        $deleted = PluginRequest::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info(sprintf('Removed %d plugin requests older than %d days.', $deleted, $days));

        return self::SUCCESS;
    }
}
