<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_statistics', function (Blueprint $table) {
            // Renommer les colonnes existantes vers les menus Sportcash
            $table->renameColumn('menu_vehicules_neufs', 'menu_informations');
            $table->renameColumn('menu_sav',             'menu_demandes');
            $table->renameColumn('menu_club_vip',        'menu_encaissement');
            $table->renameColumn('menu_agent',           'menu_plaintes');
            // menu_reclamations reste (option 5 = Réclamations)

            // Ajouter les nouvelles colonnes (options 3, 7, 8)
            $table->unsignedInteger('menu_paris')->default(0)->after('menu_demandes');
            $table->unsignedInteger('menu_conseiller')->default(0)->after('menu_plaintes');
            $table->unsignedInteger('menu_faq')->default(0)->after('menu_conseiller');

            // Supprimer le compteur agent mode
            $table->dropColumn('transferred_conversations');
        });
    }

    public function down(): void
    {
        Schema::table('daily_statistics', function (Blueprint $table) {
            $table->renameColumn('menu_informations', 'menu_vehicules_neufs');
            $table->renameColumn('menu_demandes',     'menu_sav');
            $table->renameColumn('menu_encaissement', 'menu_club_vip');
            $table->renameColumn('menu_plaintes',     'menu_agent');

            $table->dropColumn(['menu_paris', 'menu_conseiller', 'menu_faq']);

            $table->unsignedInteger('transferred_conversations')->default(0)->after('completed_conversations');
        });
    }
};
