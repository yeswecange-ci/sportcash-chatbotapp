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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique()->comment('Numéro de téléphone WhatsApp');
            $table->string('nom_prenom')->nullable()->comment('Nom complet du client');
            $table->string('email')->nullable()->comment('Email du client');
            $table->boolean('is_client')->nullable()->comment('Est-ce un client Sportcash');
            $table->string('vin')->nullable()->comment('Numéro VIN du véhicule');
            $table->string('carte_vip')->nullable()->comment('Numéro carte VIP');
            $table->unsignedInteger('interaction_count')->default(0)->comment('Nombre total d\'interactions');
            $table->unsignedInteger('conversation_count')->default(0)->comment('Nombre de conversations');
            $table->timestamp('first_interaction_at')->nullable()->comment('Première interaction');
            $table->timestamp('last_interaction_at')->nullable()->comment('Dernière interaction');
            $table->timestamps();

            // Indexes
            $table->index('phone_number');
            $table->index('is_client');
            $table->index('last_interaction_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
