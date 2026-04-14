<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('order')->default(1);
            $table->text('text');
            $table->enum('type', ['mcq', 'free_text', 'vote', 'prediction']);
            $table->json('options')->nullable();
            $table->string('correct_answer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_questions');
    }
};
