<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SPEC §10 — a member attends an event as exactly one of their accounts.
     *
     * Attendance is still recorded per account, but "one per member" cannot be
     * expressed as a constraint over `account_id` alone, since a member owns
     * several accounts. Denormalising the owning user onto the row lets the
     * database enforce it outright rather than leaving it to application code,
     * where two concurrent requests could still slip a second row through.
     */
    public function up(): void
    {
        Schema::table('calendar_event_rsvps', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->nullable()->after('account_id')
                ->constrained()->cascadeOnDelete();
        });

        // Backfill from the account's owner.
        DB::statement('
            update calendar_event_rsvps
               set user_id = accounts.user_id
              from accounts
             where accounts.id = calendar_event_rsvps.account_id
        ');

        // Existing data may already hold several accounts of one member on the
        // same event; keep the earliest and drop the rest so the unique index
        // below can be created.
        DB::statement('
            delete from calendar_event_rsvps
             where id not in (
                   select min(id) from calendar_event_rsvps
                    group by calendar_event_id, user_id
             )
        ');

        Schema::table('calendar_event_rsvps', function (Blueprint $table) {
            $table->unique(['calendar_event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('calendar_event_rsvps', function (Blueprint $table) {
            $table->dropUnique(['calendar_event_id', 'user_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
