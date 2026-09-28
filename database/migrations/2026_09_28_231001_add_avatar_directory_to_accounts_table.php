<?php

use App\Services\Accounts\MoveAvatarsToRandomDirectories;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each account's avatar moves on its own, so a failure halfway leaves every
     * moved account pointing at its new folder and the rest at their old one.
     */
    public $withinTransaction = false;

    /**
     * Avatars lived at `avatars/{account id}/…` on the public disk, so anyone
     * could fetch every player's model (and the monster they were fighting) by
     * counting. Each upload now gets its own UUID folder, recorded here.
     *
     * The move is skipped in tests: RefreshDatabase migrates before a test can
     * fake the disk, and it would act on the developer's real storage.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('avatar_directory', 80)->nullable()->after('avatar_uploaded_at');
        });

        if (app()->runningUnitTests()) {
            return;
        }

        app(MoveAvatarsToRandomDirectories::class)->run();
    }

    /** The old folder names were guessable; the moved files stay where they are. */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('avatar_directory');
        });
    }
};
