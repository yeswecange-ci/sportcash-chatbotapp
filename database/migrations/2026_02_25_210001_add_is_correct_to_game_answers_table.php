<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_answers', function (Blueprint $table) {
            $table->boolean('is_correct')->nullable()->after('answer_text');
        });
    }

    public function down(): void
    {
        Schema::table('game_answers', function (Blueprint $table) {
            $table->dropColumn('is_correct');
        });
    }
};
