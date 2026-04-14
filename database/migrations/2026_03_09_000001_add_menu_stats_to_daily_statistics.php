<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_statistics', function (Blueprint $table) {
            $table->json('menu_stats')->nullable()->after('submenu_stats')
                  ->comment('Distribution dynamique des menus : {"menu_name": {"label": count}}');
        });
    }

    public function down(): void
    {
        Schema::table('daily_statistics', function (Blueprint $table) {
            $table->dropColumn('menu_stats');
        });
    }
};
