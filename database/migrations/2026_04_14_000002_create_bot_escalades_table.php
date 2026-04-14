<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_escalades', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();           // ESC-20260414-0001
            $table->string('identifiant', 255);
            $table->string('sender', 60);                        // whatsapp:+225XXXXXXXXX
            $table->string('raison', 255)->nullable();
            $table->text('resume')->nullable();                  // résumé factuel de la conversation
            $table->string('statut', 20)->default('en_attente');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('chatwoot_conversation_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('statut');
            $table->index('chatwoot_conversation_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_escalades');
    }
};
