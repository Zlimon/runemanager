<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('username_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('old_username');
            $table->string('new_username');
            $table->timestamp('detected_at');
            $table->timestamps();

            $table->index(['account_id', 'detected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('username_histories');
    }
};
