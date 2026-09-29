<?php

use App\Services\Screenshots\MoveScreenshotsToPrivateDisk;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Each row commits on its own, so a failure halfway leaves every moved row
     * pointing at its new file and the rest at their old one.
     */
    public $withinTransaction = false;

    /**
     * Move screenshots off the public disk, where anyone could fetch them by
     * guessing their name ({@see MoveScreenshotsToPrivateDisk}). A failed copy
     * throws and stops the deploy with the originals intact. A legacy row whose
     * file is already gone is cleared here, once; after activation,
     * `screenshots:move-private` runs the same move again without clearing.
     *
     * Skipped in tests: RefreshDatabase migrates before a test can fake the
     * disks, and the move would act on the developer's real storage.
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        app(MoveScreenshotsToPrivateDisk::class)->run(pruneMissing: true);
    }

    /** One way: the public copies are gone and their old names were guessable anyway. */
    public function down(): void {}
};
