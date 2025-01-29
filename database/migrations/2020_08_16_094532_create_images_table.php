<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('image_file_name');
            $table->string('image_file_extension');
            $table->string('image_file_type');
            $table->integer('image_file_size');
            $table->timestamps();
        });

        DB::table('images')->insert(
            [
                'id' => 1,
                'image_file_name' => 'default',
                'image_file_extension' => 'png',
                'image_file_type' => 'image/png',
                'image_file_size' => 2231364
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
        Schema::dropIfExists('images');
    }
}
