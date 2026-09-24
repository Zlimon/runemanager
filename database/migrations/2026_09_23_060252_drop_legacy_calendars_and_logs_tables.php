<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops `calendars`, `logs` and `broadcasts`, three tables left over from the
 * first version of the site. None has an Eloquent model, a factory, a seeder or
 * a single reader anywhere in the application: the calendar has been served by
 * `calendar_events` since the 2026_06_02 migration, and nothing ever replaced
 * the activity log that `logs` was meant to hold.
 *
 * `broadcasts` goes with them because its only content was a foreign key into
 * `logs`; Postgres refuses to drop `logs` while that constraint stands, and
 * dropping the constraint alone would leave a dead table pointing at nothing.
 *
 * The drop order is load-bearing and must not be tidied: `broadcasts` has to
 * go before `logs`, or production fails with "cannot drop table logs because
 * other objects depend on it". SQLite does not enforce the constraint, so the
 * test suite stays green either way and will not catch a reordering.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('broadcasts');
        Schema::dropIfExists('logs');
        Schema::dropIfExists('calendars');
    }

    /**
     * Recreates the three tables as their original migrations defined them. The
     * rows are gone for good — the drop above is not reversible — so a rollback
     * only restores the schema, leaving three empty tables behind.
     */
    public function down(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('title', 50);
            $table->string('description', 100)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->integer('icon_id')->nullable();
            $table->string('event_color', 7)->default('#3490dc');
            $table->timestamps();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->string('action');
            $table->text('data')->nullable();
            $table->integer('total')->nullable();
            $table->timestamps();
        });

        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_id')->constrained();
            $table->enum('type', ['announcement', 'event', 'notification'])->default('notification');
            $table->string('icon');
            $table->string('message');
            $table->timestamps();
        });
    }
};
