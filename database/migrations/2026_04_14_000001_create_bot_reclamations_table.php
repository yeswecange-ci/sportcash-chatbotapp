<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_reclamations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();          // REC-20260414-0001
            $table->string('identifiant', 255);                 // email ou téléphone client
            $table->string('sender', 60);                       // whatsapp:+225XXXXXXXXX
            $table->string('canal', 30)->nullable();            // application|site_web|ussd|point_de_vente
            $table->string('type_reclamation', 100)->nullable();
            $table->text('infos')->nullable();                  // résumé des infos collectées
            $table->string('priorite', 10)->default('normale'); // normale|haute
            $table->string('statut', 20)->default('en_attente');// en_attente|en_cours|resolue|fermee
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('statut');
            $table->index('priorite');
            $table->index('canal');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_reclamations');
    }
};
