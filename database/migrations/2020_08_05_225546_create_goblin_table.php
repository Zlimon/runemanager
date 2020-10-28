<?php
// TODO remove later
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoblinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goblin', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('bones')->default(0)->unsigned();
            $table->integer('water_rune')->default(0)->unsigned();
            $table->integer('coins')->default(0)->unsigned();
            $table->integer('hammer')->default(0)->unsigned();
            $table->integer('beer')->default(0)->unsigned();
            $table->integer('goblin_mail')->default(0)->unsigned();
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
        Schema::dropIfExists('goblin');
    }
}
