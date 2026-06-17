<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Skill-icon slug for the current activity (e.g. "fishing", "ranged"),
            // so the UI can show the matching icon next to the activity text.
            $table->string('activity_icon', 40)->nullable()->after('activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('activity_icon');
        });
    }
};
