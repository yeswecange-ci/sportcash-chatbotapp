<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\DailyStatistic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloseAbandonedConversations extends Command
{
    protected $signature = 'bot-tracking:close-abandoned {--hours=24 : Inactivity threshold in hours}';

    protected $description = 'Mark active conversations with no activity for X hours as abandoned';

    public function handle(): int
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->warn('Database connection error: ' . $e->getMessage());
            return Command::SUCCESS;
        }

        $hours = (int) $this->option('hours');
        $threshold = now()->subHours($hours);

        // Récupérer les IDs + dates des conversations à abandonner (pour mettre à jour les stats par jour)
        $rows = Conversation::where('status', 'active')
            ->where('last_activity_at', '<', $threshold)
            ->select('id', 'started_at', 'last_activity_at')
            ->get();

        if ($rows->isEmpty()) {
            $this->info('[BotTracking] Aucune conversation à abandonner.');
            return Command::SUCCESS;
        }

        $ids = $rows->pluck('id');

        // Mise à jour en masse : ended_at = last_activity_at, durée calculée en SQL
        DB::table('conversations')
            ->whereIn('id', $ids)
            ->update([
                'status'           => 'abandoned',
                'ended_at'         => DB::raw('last_activity_at'),
                'duration_seconds' => DB::raw('TIMESTAMPDIFF(SECOND, started_at, last_activity_at)'),
                'updated_at'       => now(),
            ]);

        // Incrémenter abandoned_conversations dans DailyStatistic par jour concerné
        $byDay = $rows->groupBy(fn($r) => $r->started_at->toDateString());

        foreach ($byDay as $date => $group) {
            DailyStatistic::firstOrCreate(['date' => $date], ['submenu_stats' => []])
                ->increment('abandoned_conversations', $group->count());
        }

        $count = $rows->count();
        $this->info("[BotTracking] {$count} conversation(s) passée(s) en 'abandoned' (inactivité > {$hours}h).");

        return Command::SUCCESS;
    }
}
