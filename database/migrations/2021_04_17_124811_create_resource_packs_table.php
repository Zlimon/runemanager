<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcePacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resource_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias');
            $table->string('version');
            $table->string('author');
            $table->string('url');
            $table->timestamps();
        });

        // TODO refactor to use Storage facade instead
        if (!File::exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

        Artisan::call(
            'resourcepack:fetch',
            [
                'name' => 'pack-osrs-dark',
                '--use' => 'yes',
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
        Schema::dropIfExists('resource_packs');
    }
}
