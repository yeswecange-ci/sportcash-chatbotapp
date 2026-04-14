<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Déclencher les campagnes planifiées toutes les minutes
Schedule::command('campaigns:send-scheduled')->everyMinute()->withoutOverlapping();

// Synchroniser les stats support client toutes les heures
Schedule::command('stats:sync --period=all')->hourly()->withoutOverlapping()->runInBackground();

// Gamification : auto-activation/clôture jeux + nettoyage participations bloquées
Schedule::command('gamification:sync-status')->everyMinute()->withoutOverlapping();

// Bot-tracking : clôturer les conversations inactives depuis 24h
Schedule::command('bot-tracking:close-abandoned')->hourly()->withoutOverlapping();
