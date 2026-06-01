<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // RuneLite's Client.getAccountHash() returns a long; store as string for safety + flexibility.
            $table->string('account_hash')->nullable()->unique()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['account_hash']);
            $table->dropColumn('account_hash');
        });
    }
};
