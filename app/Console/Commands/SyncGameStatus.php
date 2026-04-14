<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\GameParticipation;
use Illuminate\Console\Command;

class SyncGameStatus extends Command
{
    protected $signature = 'gamification:sync-status';

    protected $description = 'Active/ferme automatiquement les jeux selon leurs dates, et passe en abandoned les participations bloquées';

    public function handle(): int
    {
        // 1. Activer les jeux en brouillon dont la start_date est passée
        $activated = Game::where('status', 'draft')
            ->whereNotNull('start_date')
            ->where('start_date', '<=', now())
            ->update(['status' => 'active']);

        // 2. Fermer les jeux actifs dont la end_date est passée
        $gameIdsToClose = Game::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now())
            ->pluck('id');

        $closed = 0;
        if ($gameIdsToClose->isNotEmpty()) {
            $closed = Game::whereIn('id', $gameIdsToClose)->update(['status' => 'closed']);

            // Abandonner toutes les participations encore en cours sur ces jeux
            GameParticipation::whereIn('game_id', $gameIdsToClose)
                ->where('status', 'started')
                ->update(['status' => 'abandoned']);
        }

        // 3. Passer en 'abandoned' les participations 'started' depuis plus de 24h
        //    (Le participant a commencé mais n'a jamais terminé le flow)
        $abandoned = GameParticipation::where('status', 'started')
            ->where('started_at', '<', now()->subHours(24))
            ->update(['status' => 'abandoned']);

        $this->info("[Gamification] Jeux activés: {$activated} | Jeux fermés: {$closed} | Participations abandonnées: {$abandoned}");

        return Command::SUCCESS;
    }
}
