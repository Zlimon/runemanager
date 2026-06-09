<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Roster pre-creation (SPEC §5.2): the clan/group owner seeds accounts that
     * aren't yet linked to a website user, so `user_id` becomes nullable (claimed
     * on the owner's first plugin login). `clan_name` is dropped — the whole site
     * is one clan, so the rank alone drives the role.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->dropColumn('clan_name');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('clan_name')->nullable()->after('account_type');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
