<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_reclamations', function (Blueprint $table) {
            $table->unsignedBigInteger('chatwoot_conversation_id')->nullable()->after('notes');
            $table->index('chatwoot_conversation_id');
        });
    }

    public function down(): void
    {
        Schema::table('bot_reclamations', function (Blueprint $table) {
            $table->dropIndex(['chatwoot_conversation_id']);
            $table->dropColumn('chatwoot_conversation_id');
        });
    }
};
