<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'ENUM pour ajouter les event_types manquants
        // Note: On doit lister TOUTES les valeurs (anciennes + nouvelles)
        DB::statement("ALTER TABLE conversation_events MODIFY COLUMN event_type ENUM(
            'flow_start',
            'menu_display',
            'menu_choice',
            'free_input',
            'message_sent',
            'message_received',
            'document_sent',
            'agent_transfer',
            'agent_takeover',
            'agent_message',
            'agent_response',
            'timeout_warning',
            'timeout',
            'flow_complete',
            'conversation_closed',
            'invalid_input',
            'error'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer l'ENUM original (sans les nouveaux types)
        DB::statement("ALTER TABLE conversation_events MODIFY COLUMN event_type ENUM(
            'flow_start',
            'menu_display',
            'menu_choice',
            'free_input',
            'message_sent',
            'document_sent',
            'agent_transfer',
            'agent_response',
            'timeout_warning',
            'timeout',
            'flow_complete',
            'invalid_input',
            'error'
        ) NOT NULL");
    }
};
