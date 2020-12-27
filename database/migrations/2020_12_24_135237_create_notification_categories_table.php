<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category');
        });

        DB::table('notification_categories')->insert(
            [
                ["category" => "skill"],
                ["category" => "boss"],
                ["category" => "raid"],
                ["category" => "account"],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_categories');
    }
}
