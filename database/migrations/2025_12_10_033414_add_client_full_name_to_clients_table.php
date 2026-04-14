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
        Schema::table('clients', function (Blueprint $table) {
            // Renommer nom_prenom en whatsapp_profile_name pour plus de clarté
            $table->renameColumn('nom_prenom', 'whatsapp_profile_name');

            // Ajouter le champ pour le vrai nom du client (saisi manuellement)
            $table->string('client_full_name')->nullable()->after('phone_number')
                ->comment('Nom complet réel du client (saisi manuellement)');

            // Ajouter un index pour recherche rapide
            $table->index('client_full_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Supprimer l'index
            $table->dropIndex(['client_full_name']);

            // Supprimer le champ
            $table->dropColumn('client_full_name');

            // Renommer en arrière
            $table->renameColumn('whatsapp_profile_name', 'nom_prenom');
        });
    }
};
