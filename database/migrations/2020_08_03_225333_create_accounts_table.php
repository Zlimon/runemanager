<?php

use App\Enums\AccountTypesEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $accountTypes = array_map(function ($accountType) {
            return $accountType->value;
        }, AccountTypesEnum::cases()
        );

        Schema::create('accounts', function (Blueprint $table) use ($accountTypes) {
            $table->id();
            // Nullable so a clan/group owner can pre-seed roster accounts that are
            // claimed on the member's first plugin login.
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('account_hash')->nullable()->unique();
            $table->enum('account_type', $accountTypes);
            $table->smallInteger('clan_rank')->nullable();
            $table->string('clan_title')->nullable();
            $table->string('username', 13)->unique();
            $table->integer('rank')->default(0);
            $table->integer('level')->default(32); // Minimum total level
            $table->bigInteger('xp')->default(0);
            $table->timestamp('avatar_uploaded_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            // Live position (Live Map) + world.
            $table->integer('world_x')->nullable();
            $table->integer('world_y')->nullable();
            $table->unsignedTinyInteger('world_plane')->nullable();
            $table->unsignedInteger('world_number')->nullable();
            $table->timestamp('position_updated_at')->nullable();

            // Live vitals (status orbs).
            $table->unsignedSmallInteger('hitpoints')->nullable();
            $table->unsignedSmallInteger('hitpoints_max')->nullable();
            $table->unsignedSmallInteger('prayer')->nullable();
            $table->unsignedSmallInteger('prayer_max')->nullable();
            $table->unsignedTinyInteger('run_energy')->nullable();
            $table->unsignedTinyInteger('special_attack')->nullable();
            $table->timestamp('vitals_updated_at')->nullable();

            // Current in-game activity + area.
            $table->string('activity', 60)->nullable();
            $table->string('activity_icon', 40)->nullable();
            $table->string('location', 60)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
