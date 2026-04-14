<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participation_id')->constrained('game_participations')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('game_questions')->cascadeOnDelete();
            $table->text('answer_text');
            $table->datetime('answered_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_answers');
    }
};
