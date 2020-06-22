<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarrowsChestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barrows_chests', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('torags_helm')->default(0)->unsigned();
            $table->integer('guthans_chainskirt')->default(0)->unsigned();
            $table->integer('dharoks_platelegs')->default(0)->unsigned();
            $table->integer('karils_coif')->default(0)->unsigned();
            $table->integer('karils_leathertop')->default(0)->unsigned();
            $table->integer('dharoks_platebody')->default(0)->unsigned();
            $table->integer('ahrims_staff')->default(0)->unsigned();
            $table->integer('bolt_rack')->default(0)->unsigned();
            $table->integer('ahrims_robetop')->default(0)->unsigned();
            $table->integer('guthans_platebody')->default(0)->unsigned();
            $table->integer('veracs_flail')->default(0)->unsigned();
            $table->integer('guthans_helm')->default(0)->unsigned();
            $table->integer('torags_platelegs')->default(0)->unsigned();
            $table->integer('torags_hammers')->default(0)->unsigned();
            $table->integer('veracs_helm')->default(0)->unsigned();
            $table->integer('guthans_warspear')->default(0)->unsigned();
            $table->integer('ahrims_robeskirt')->default(0)->unsigned();
            $table->integer('karils_crossbow')->default(0)->unsigned();
            $table->integer('ahrims_hood')->default(0)->unsigned();
            $table->integer('torags_platebody')->default(0)->unsigned();
            $table->integer('dharoks_greataxe')->default(0)->unsigned();
            $table->integer('veracs_brassard')->default(0)->unsigned();
            $table->integer('veracs_plateskirt')->default(0)->unsigned();
            $table->integer('dharoks_helm')->default(0)->unsigned();
            $table->integer('karils_leatherskirt')->default(0)->unsigned();
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
        Schema::dropIfExists('barrows_chests');
    }
}
