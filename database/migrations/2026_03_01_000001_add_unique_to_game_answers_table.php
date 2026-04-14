<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer les doublons éventuels avant d'ajouter la contrainte
        DB::statement('
            DELETE ga1 FROM game_answers ga1
            INNER JOIN game_answers ga2
            WHERE ga1.id > ga2.id
              AND ga1.participation_id = ga2.participation_id
              AND ga1.question_id = ga2.question_id
        ');

        Schema::table('game_answers', function (Blueprint $table) {
            $table->unique(['participation_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::table('game_answers', function (Blueprint $table) {
            $table->dropUnique(['participation_id', 'question_id']);
        });
    }
};
