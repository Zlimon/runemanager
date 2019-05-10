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
        Schema::table('accounts', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

        foreach ($skills as $skill) {
            Schema::table($skill, function($table) {
                $table->foreign('account_id')->references('id')->on('accounts');
            });
        }

        Schema::table('account_tasks', function($table) {
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('task_id')->references('id')->on('tasks');
        });

        Schema::table('news_posts', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('image_id')->references('id')->on('images');
        });
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
