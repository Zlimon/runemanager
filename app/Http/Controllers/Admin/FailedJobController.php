<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Support\Facades\Artisan;

/**
 * Retry or discard queue jobs from `failed_jobs`, shown on the admin
 * dashboard's health panel (App\Services\Instance\SystemHealth). Jobs are
 * addressed by `uuid` throughout — config/queue.php's `failed.driver` is
 * `database-uuids`, whose provider looks jobs up by that column, not the
 * table's numeric `id`.
 */
class FailedJobController extends Controller
{
    /**
     * Re-queue one failed job, or every failed job when none is named —
     * mirrors UserTokenController::destroy's optional-target shape.
     */
    public function retry(?string $failedJob = null): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => $failedJob !== null ? [$failedJob] : ['all']]);

        return back()->banner($failedJob !== null
            ? 'Job queued for retry.'
            : 'All failed jobs queued for retry.');
    }

    public function forget(string $failedJob, FailedJobProviderInterface $failer): RedirectResponse
    {
        $failer->forget($failedJob);

        return back()->banner('Failed job removed.');
    }
}
