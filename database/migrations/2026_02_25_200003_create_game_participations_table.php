<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number');
            $table->string('participant_name')->nullable();
            $table->enum('status', ['started', 'completed', 'abandoned'])->default('started');
            $table->datetime('started_at');
            $table->datetime('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'phone_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_participations');
    }
};
