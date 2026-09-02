<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('character_episode', function (Blueprint $table) {
            $table->foreignId('character_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('episode_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['character_id', 'episode_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_episode');
    }
};
