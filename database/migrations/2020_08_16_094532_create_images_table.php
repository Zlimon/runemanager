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

        // TODO refactor to use Storage facade instead
        if (!File::exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

        if (!File::exists(public_path('storage/newspost'))) {
            File::makeDirectory(public_path('storage/newspost'));
        }

        File::copy(public_path('images/newspost_default.png'), public_path('storage/newspost/newspost_default.png'));

        DB::table('images')->insert(
            [
                'image_file_name' => File::name(public_path('storage/newspost/newspost_default.png')),
                'image_file_extension' => File::extension(public_path('storage/newspost/newspost_default.png')),
                'image_file_type' => File::mimeType(public_path('storage/newspost/newspost_default.png')),
                'image_file_size' => File::size(public_path('storage/newspost/newspost_default.png')),
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
