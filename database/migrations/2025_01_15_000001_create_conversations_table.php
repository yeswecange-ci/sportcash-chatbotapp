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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->comment('Twilio Flow Session ID');
            $table->string('phone_number', 20)->index();
            $table->string('nom_prenom')->nullable();
            $table->boolean('is_client')->nullable();
            $table->string('email')->nullable();
            $table->string('vin', 17)->nullable()->comment('Vehicle Identification Number');
            $table->string('carte_vip')->nullable();
            
            // Chatwoot integration
            $table->unsignedBigInteger('chatwoot_conversation_id')->nullable();
            $table->string('chatwoot_contact_id')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'active',
                'completed', 
                'transferred',
                'timeout',
                'abandoned'
            ])->default('active');
            
            // Parcours tracking
            $table->string('current_menu')->nullable()->comment('Dernier menu affiché');
            $table->json('menu_path')->nullable()->comment('Chemin complet du parcours');
            $table->string('last_widget')->nullable();
            
            // Timestamps
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('transferred_at')->nullable();
            
            $table->timestamps();
            
            // Index pour les recherches fréquentes
            $table->index('status');
            $table->index('started_at');
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
