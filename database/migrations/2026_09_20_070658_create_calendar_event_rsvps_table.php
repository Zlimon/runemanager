<?php

use App\Models\Account;
use App\Models\CalendarEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who is attending an event. RSVP is per ACCOUNT rather than per
     * user: a member with several accounts signs up with the one they'll show up
     * on. A row means "attending"; un-RSVPing deletes it.
     */
    public function up(): void
    {
        Schema::create('calendar_event_rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CalendarEvent::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Account::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['calendar_event_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_rsvps');
    }
};
