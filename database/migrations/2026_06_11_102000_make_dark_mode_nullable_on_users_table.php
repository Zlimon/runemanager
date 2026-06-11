<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A null dark_mode now means "no explicit choice" — the viewer follows the
     * instance default (set by the owner) and, failing that, the resource pack's
     * own flag. A concrete true/false is only stored once the user toggles.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('dark_mode')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('dark_mode')->default(false)->change();
        });
    }
};
