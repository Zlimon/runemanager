<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

        // foreach ($skills as $skill) {
        //     Schema::table($skill, function($table) {
        //         $table->foreign('user_id')->references('id')->on('users');
        //     });
        // }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
