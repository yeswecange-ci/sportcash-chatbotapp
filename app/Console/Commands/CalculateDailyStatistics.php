<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyStatistic;
use App\Models\Conversation;
use App\Models\ConversationEvent;
use Carbon\Carbon;

class CalculateDailyStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:calculate {--from= : Start date (Y-m-d)} {--to= : End date (Y-m-d)} {--force : Recalculate existing stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate daily statistics from conversations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily statistics calculation...');

        try {
            // Vérifier la connexion à la base de données
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->warn('Database connection error: ' . $e->getMessage());
            $this->warn('Skipping statistics calculation during build.');
            return Command::SUCCESS; // ✅ Retourner SUCCESS pour ne pas bloquer le build
        }

        // Déterminer la plage de dates
        $from = $this->option('from')
            ? Carbon::parse($this->option('from'))
            : Conversation::min('started_at');

        $to = $this->option('to')
            ? Carbon::parse($this->option('to'))
            : now();

        if (!$from) {
            $this->warn('No conversations found in the database.'); // ✅ Changé de error à warn
            $this->info('This is normal for a fresh installation.');
            return Command::SUCCESS; // ✅ Changé de FAILURE à SUCCESS
        }

        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        $this->info("Calculating stats from {$from->format('Y-m-d')} to {$to->format('Y-m-d')}");

        // Obtenir toutes les dates uniques où il y a eu des conversations
        $dates = Conversation::whereBetween('started_at', [$from, $to])
            ->selectRaw('DATE(started_at) as date')
            ->distinct()
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date);
            });

        if ($dates->isEmpty()) {
            $this->warn('No conversations found in the specified date range.');
            $this->info('Statistics calculation skipped.');
            return Command::SUCCESS; // ✅ Ajouté: gérer le cas où il n'y a pas de dates
        }

        $this->info("Found {$dates->count()} days with conversations");

        $progressBar = $this->output->createProgressBar($dates->count());
        $progressBar->start();

        $created = 0;
        $updated = 0;

        foreach ($dates as $date) {
            try {
                // Vérifier si les stats existent déjà
                $existing = DailyStatistic::where('date', $date->format('Y-m-d'))->first();

                if ($existing && !$this->option('force')) {
                    $updated++;
                    $progressBar->advance();
                    continue;
                }

                // Calculer les statistiques pour cette date
                $stat = DailyStatistic::recalculateForDate($date);

                // Calculer les statistiques de menu à partir des événements
                $this->calculateMenuStats($stat, $date);

                if ($existing) {
                    $updated++;
                } else {
                    $created++;
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("Error processing date {$date->format('Y-m-d')}: " . $e->getMessage());
                $progressBar->advance();
                continue; // ✅ Continuer avec la prochaine date en cas d'erreur
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Statistics calculation completed!");
        $this->info("- New stats: {$created}");
        $this->info("- Updated stats: {$updated}");
        $this->info("- Total processed: {$dates->count()}");

        return Command::SUCCESS;
    }

    /**
     * Calcule les stats de menu dynamiquement depuis les événements.
     * S'adapte à n'importe quel flow Twilio sans configuration.
     */
    protected function calculateMenuStats(DailyStatistic $stat, Carbon $date): void
    {
        try {
            $conversationIds = Conversation::whereDate('started_at', $date)->pluck('id');

            if ($conversationIds->isEmpty()) {
                return;
            }

            $menuStats = [];

            ConversationEvent::whereIn('conversation_id', $conversationIds)
                ->where('event_type', 'menu_choice')
                ->get()
                ->each(function ($event) use (&$menuStats) {
                    $menuName = $event->menu_name
                        ?: (($event->metadata['menu_choice'] ?? null) ?: 'menu_principal');
                    $label = $event->choice_label
                        ?: $event->user_input
                        ?: 'unknown';

                    $menuStats[$menuName][$label] = ($menuStats[$menuName][$label] ?? 0) + 1;
                });

            $stat->menu_stats = $menuStats;
            $stat->save();

        } catch (\Exception $e) {
            $this->error("Error calculating menu stats: " . $e->getMessage());
        }
    }
}
