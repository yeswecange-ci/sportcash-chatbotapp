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
        Schema::create('conversation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Event identification
            $table->enum('event_type', [
                'flow_start',           // Début de conversation
                'menu_display',         // Affichage d'un menu
                'menu_choice',          // Choix dans un menu (1, 2, 3...)
                'free_input',           // Saisie libre (nom, réclamation, etc.)
                'message_sent',         // Message envoyé par le bot
                'document_sent',        // PDF/média envoyé
                'agent_transfer',       // Transfert vers Chatwoot
                'agent_response',       // Réponse d'un agent (via Chatwoot webhook)
                'timeout_warning',      // Avertissement avant timeout
                'timeout',              // Timeout atteint
                'flow_complete',        // Conversation terminée normalement
                'invalid_input',        // Entrée non reconnue (noMatch)
                'error'                 // Erreur technique
            ]);
            
            // Widget Twilio
            $table->string('widget_name')->nullable()->comment('Nom du widget Twilio');
            $table->string('widget_type')->nullable()->comment('Type: send-message, split, etc.');
            
            // User input
            $table->text('user_input')->nullable()->comment('Ce que l\'utilisateur a tapé');
            $table->string('expected_input_type')->nullable()->comment('menu_choice, free_text, yes_no');
            
            // Bot response
            $table->text('bot_message')->nullable()->comment('Message envoyé par le bot');
            $table->string('media_url')->nullable()->comment('URL du média envoyé');
            
            // Navigation
            $table->string('menu_name')->nullable()->comment('Nom du menu (menu_prin, vn, sav...)');
            $table->string('choice_label')->nullable()->comment('Label du choix (ex: Véhicules neufs)');
            $table->json('menu_path')->nullable()->comment('Chemin actuel dans le flow');
            
            // Metadata
            $table->json('metadata')->nullable()->comment('Données additionnelles');
            $table->integer('response_time_ms')->nullable()->comment('Temps de réponse utilisateur');
            
            // Timestamps
            $table->timestamp('event_at')->useCurrent();
            $table->timestamps();
            
            // Index
            $table->index('event_type');
            $table->index('event_at');
            $table->index('widget_name');
            $table->index(['conversation_id', 'event_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_events');
    }
};
