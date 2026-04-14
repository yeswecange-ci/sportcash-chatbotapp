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
        Schema::create('daily_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            
            // Compteurs globaux
            $table->unsignedInteger('total_conversations')->default(0);
            $table->unsignedInteger('unique_users')->default(0);
            $table->unsignedInteger('new_users')->default(0);
            $table->unsignedInteger('returning_users')->default(0);
            
            // Statuts
            $table->unsignedInteger('completed_conversations')->default(0);
            $table->unsignedInteger('transferred_conversations')->default(0);
            $table->unsignedInteger('timeout_conversations')->default(0);
            $table->unsignedInteger('abandoned_conversations')->default(0);
            
            // Par menu principal
            $table->unsignedInteger('menu_vehicules_neufs')->default(0);
            $table->unsignedInteger('menu_sav')->default(0);
            $table->unsignedInteger('menu_reclamations')->default(0);
            $table->unsignedInteger('menu_club_vip')->default(0);
            $table->unsignedInteger('menu_agent')->default(0);
            
            // Sous-menus populaires
            $table->json('submenu_stats')->nullable();
            
            // Temps moyens
            $table->unsignedInteger('avg_session_duration_seconds')->nullable();
            $table->unsignedInteger('avg_response_time_ms')->nullable();
            
            // Clients vs Non-clients
            $table->unsignedInteger('clients_count')->default(0);
            $table->unsignedInteger('non_clients_count')->default(0);
            
            // Erreurs
            $table->unsignedInteger('invalid_inputs_count')->default(0);
            $table->unsignedInteger('errors_count')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_statistics');
    }
};
