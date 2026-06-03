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
            $table->string('tags');
            $table->boolean('dark_mode')->default(false);
            // SPEC §6 — sampled from the pack assets at install time and emitted
            // as CSS variables (var(--pack-bg) / var(--pack-accent)) so flat
            // panels can pick up the pack's palette without textured tiles.
            // Nullable: extraction can fall through for an exotic pack and we
            // just fall back to DaisyUI defaults.
            $table->string('background_color', 7)->nullable();
            $table->string('accent_color', 7)->nullable();
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
        Schema::dropIfExists('resource_packs');
    }
}
