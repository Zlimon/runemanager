<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An administrator can suspend a user: the user can no longer log in on the
     * website or the plugin until the suspension is lifted. The suspending
     * admin is kept for the audit trail and nulled if that admin is deleted.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->foreignId('suspended_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('suspended_by_user_id');
            $table->dropColumn(['suspended_at', 'suspension_reason']);
        });
    }
};
