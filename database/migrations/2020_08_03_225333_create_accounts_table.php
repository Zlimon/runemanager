<?php

use App\Helpers\Helper;
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
            }, \App\Enums\AccountTypesEnum::cases()
        );

        Schema::create('accounts', function (Blueprint $table) use ($accountTypes) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->enum('account_type', $accountTypes);
            $table->string('username', 13)->unique();
            $table->integer('rank')->default(0);
            $table->integer('level')->default(32); // Minimum total level
            $table->bigInteger('xp')->default(0);
            $table->boolean('online')->default(false);
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
