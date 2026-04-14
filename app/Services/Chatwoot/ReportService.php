<?php

namespace App\Services\Chatwoot;

use App\Models\SupportAgentStat;
use App\Models\SupportDailyStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportService
{
    public function __construct(
        private ChatwootClient $client
    ) {}

    // ═══════════════════════════════════════════════════════
    // PUBLIC — Dashboard & Statistics
    // ═══════════════════════════════════════════════════════

    /**
     * Stats globales pour le dashboard (DB-first, API fallback)
     */
    public function getDashboardStats(string $period = 'today'): array
    {
        $dbRow = SupportDailyStat::where('date', today()->toDateString())
            ->where('period', $period)
            ->first();

        // Construire les courbes de tendance depuis les snapshots journaliers
        $trend        = $this->buildTrendFromDailyRows($period, 'conversations_count');
        $resTrend     = $this->buildTrendFromDailyRows($period, 'resolutions_count');
        $inTrend      = $this->buildTrendFromDailyRows($period, 'incoming_messages_count');
        $outTrend     = $this->buildTrendFromDailyRows($period, 'outgoing_messages_count');

        if ($dbRow) {
            return [
                'summary'             => $this->summaryFromRow($dbRow),
                'conversations_count' => $trend,
                'resolutions'         => $resTrend,
                'incoming_messages'   => $inTrend,
                'outgoing_messages'   => $outTrend,
                'avg_first_response'  => [],
                'avg_resolution_time' => [],
                'counts'              => [
                    'open_count'       => $dbRow->open_count,
                    'pending_count'    => $dbRow->pending_count,
                    'resolved_count'   => $dbRow->resolved_count,
                    'all_count'        => $dbRow->open_count,
                    'mine_count'       => 0,
                    'assigned_count'   => $dbRow->open_count,
                    'unassigned_count' => 0,
                ],
                'source' => 'db',
            ];
        }

        // Fallback API
        [$since, $until] = $this->resolvePeriod($period);
        return [
            'summary'             => $this->safeAccountSummary($since, $until),
            'conversations_count' => $this->safeReport('conversations_count', $since, $until) ?: $trend,
            'resolutions'         => $this->safeReport('resolutions_count', $since, $until) ?: $resTrend,
            'incoming_messages'   => $this->safeReport('incoming_messages_count', $since, $until) ?: $inTrend,
            'outgoing_messages'   => $this->safeReport('outgoing_messages_count', $since, $until) ?: $outTrend,
            'avg_first_response'  => $this->safeReport('avg_first_response_time', $since, $until),
            'avg_resolution_time' => $this->safeReport('avg_resolution_time', $since, $until),
            'counts'              => $this->safeCounts(),
            'source'              => 'api',
        ];
    }

    /**
     * Stats completes pour la page statistiques (DB-first, API fallback)
     */
    public function getFullStats(string $period = 'week'): array
    {
        $dbRow = SupportDailyStat::where('date', today()->toDateString())
            ->where('period', $period)
            ->first();

        $trend    = $this->buildTrendFromDailyRows($period, 'conversations_count');
        $resTrend = $this->buildTrendFromDailyRows($period, 'resolutions_count');
        $inTrend  = $this->buildTrendFromDailyRows($period, 'incoming_messages_count');
        $outTrend = $this->buildTrendFromDailyRows($period, 'outgoing_messages_count');

        if ($dbRow) {
            // Si les tendances messages sont toutes à 0 (API Reports indisponible lors du sync),
            // on tente un appel direct pour récupérer les vraies données.
            $allInZero  = empty(array_filter(array_column($inTrend,  'value')));
            $allOutZero = empty(array_filter(array_column($outTrend, 'value')));

            if ($allInZero || $allOutZero) {
                [$since, $until] = $this->resolvePeriod($period);
                $inTrend  = $this->safeReport('incoming_messages_count', $since, $until) ?: $inTrend;
                $outTrend = $this->safeReport('outgoing_messages_count', $since, $until) ?: $outTrend;
            }

            return [
                'summary'           => $this->summaryFromRow($dbRow),
                'counts'            => [
                    'open_count'       => $dbRow->open_count,
                    'pending_count'    => $dbRow->pending_count,
                    'resolved_count'   => $dbRow->resolved_count,
                    'all_count'        => $dbRow->open_count,
                    'mine_count'       => 0,
                    'assigned_count'   => $dbRow->open_count,
                    'unassigned_count' => 0,
                ],
                'trends'            => $trend,
                'resolution_trends' => $resTrend,
                'incoming_trends'   => $inTrend,
                'outgoing_trends'   => $outTrend,
                'period'            => $this->resolvePeriodTimestamps($period),
                'source'            => 'db',
            ];
        }

        // Fallback API
        [$since, $until] = $this->resolvePeriod($period);
        $accountSummary = $this->safeAccountSummary($since, $until);
        $counts = $this->safeCounts();

        return [
            'summary'           => $accountSummary,
            'counts'            => $counts,
            'trends'            => $this->safeReport('conversations_count', $since, $until) ?: $trend,
            'resolution_trends' => $this->safeReport('resolutions_count', $since, $until) ?: $resTrend,
            'incoming_trends'   => $this->safeReport('incoming_messages_count', $since, $until) ?: $inTrend,
            'outgoing_trends'   => $this->safeReport('outgoing_messages_count', $since, $until) ?: $outTrend,
            'period'            => ['since' => $since, 'until' => $until],
            'source'            => 'api',
        ];
    }

    /**
     * Classement des agents (DB-first, API fallback)
     */
    public function getAgentLeaderboard(string $period = 'week'): array
    {
        $rows = SupportAgentStat::where('date', today()->toDateString())
            ->where('period', $period)
            ->orderByDesc('conversations_count')
            ->get();

        if ($rows->isNotEmpty()) {
            return $rows->map(fn($r) => [
                'id'     => $r->chatwoot_agent_id,
                'name'   => $r->agent_name,
                'email'  => $r->agent_email,
                'metric' => [
                    'conversations_count'     => $r->conversations_count,
                    'resolutions_count'       => $r->resolutions_count,
                    'avg_first_response_time' => $r->avg_first_response_time,
                    'avg_resolution_time'     => $r->avg_resolution_time,
                ],
            ])->values()->toArray();
        }

        // Fallback API
        [$since, $until] = $this->resolvePeriod($period);
        try {
            $summary = $this->client->getAgentSummary($since, $until);
            $entries = $summary['data'] ?? $summary;
            return collect($entries)->map(fn($e) => [
                'id'     => $e['id'] ?? null,
                'name'   => $e['name'] ?? null,
                'email'  => $e['email'] ?? null,
                'metric' => $e['metric'] ?? [
                    'conversations_count'     => $e['conversations_count'] ?? 0,
                    'resolutions_count'       => $e['resolutions_count'] ?? 0,
                    'avg_first_response_time' => $e['avg_first_response_time'] ?? 0,
                    'avg_resolution_time'     => $e['avg_resolution_time'] ?? 0,
                ],
            ])->values()->toArray();
        } catch (\Exception $e) {
            Log::warning('[ReportService] Agent summary unavailable', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Derniere date de synchronisation
     */
    public function lastSyncedAt(): ?\Carbon\Carbon
    {
        return SupportDailyStat::lastSyncedAt();
    }

    // ═══════════════════════════════════════════════════════
    // CONSTRUCTION DES COURBES depuis snapshots journaliers
    // ═══════════════════════════════════════════════════════

    /**
     * Construit un tableau [{timestamp, value}] couvrant toute la plage de la periode.
     * Les jours sans snapshot = 0. Garantit au minimum 2 points pour que la courbe s'affiche.
     */
    private function buildTrendFromDailyRows(string $period, string $column): array
    {
        [$from, $steps] = match ($period) {
            'today'   => [Carbon::today(),                  1],
            'week'    => [Carbon::now()->startOfWeek(),     7],
            'month'   => [Carbon::now()->startOfMonth(),   Carbon::now()->day],
            'quarter' => [Carbon::now()->firstOfQuarter(), Carbon::now()->diffInDays(Carbon::now()->firstOfQuarter()) + 1],
            default   => [Carbon::now()->subDays(6),        7],
        };

        // Récupérer les snapshots existants indexés par date
        $rows = SupportDailyStat::where('period', 'day')
            ->whereDate('date', '>=', $from->toDateString())
            ->orderBy('date')
            ->get(['date', $column])
            ->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

        // Construire la plage complète (toujours au moins 2 points)
        $points = [];
        $end    = Carbon::today();

        for ($i = 0; $i < $steps; $i++) {
            $day     = $from->copy()->addDays($i);
            $dateKey = $day->toDateString();

            // Ne pas dépasser aujourd'hui
            if ($day->gt($end)) break;

            $value = isset($rows[$dateKey]) ? (int) $rows[$dateKey]->$column : 0;

            $points[] = [
                'timestamp' => $day->startOfDay()->timestamp,
                'value'     => $value,
            ];
        }

        // Si pas de données du tout, générer la plage avec des zéros
        // pour que le graphique ait au moins sa structure visible
        if (empty($points) || (count($points) === 1 && $points[0]['value'] === 0)) {
            return $this->emptyTrendSkeleton($period, $column);
        }

        return $points;
    }

    /**
     * Squelette de tendance vide sur la plage — affiché quand aucune donnée n'est disponible.
     * La valeur d'aujourd'hui est prise depuis le résumé de période si disponible.
     */
    private function emptyTrendSkeleton(string $period, string $column): array
    {
        $days = match ($period) {
            'today'   => 1,
            'week'    => 7,
            'month'   => 30,
            'quarter' => 90,
            default   => 7,
        };

        // Valeur du dernier résumé pour aujourd'hui
        $periodRow = SupportDailyStat::where('date', today()->toDateString())
            ->where('period', $period)
            ->first();
        $todayValue = $periodRow ? (int) ($periodRow->$column ?? 0) : 0;

        $from   = Carbon::now()->subDays($days - 1)->startOfDay();
        $points = [];

        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i);
            $points[] = [
                'timestamp' => $day->timestamp,
                'value'     => $day->isToday() ? $todayValue : 0,
            ];
        }

        return $points;
    }

    // ═══════════════════════════════════════════════════════
    // LEGACY helpers
    // ═══════════════════════════════════════════════════════

    public function getAgentPerformance(string $period = 'week'): array
    {
        [$since, $until] = $this->resolvePeriod($period);
        try {
            return $this->client->getAgentReport('conversations_count', $since, $until);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getConversationTrends(string $period = 'week'): array
    {
        $trend = $this->buildTrendFromDailyRows($period, 'conversations_count');
        if (!empty($trend)) return $trend;

        [$since, $until] = $this->resolvePeriod($period);
        try {
            return $this->client->getAccountReport('conversations_count', $since, $until);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getMessageTrends(string $period = 'week'): array
    {
        return [
            'incoming' => $this->buildTrendFromDailyRows($period, 'incoming_messages_count'),
            'outgoing' => $this->buildTrendFromDailyRows($period, 'outgoing_messages_count'),
        ];
    }

    public function getResolutionTrends(string $period = 'week'): array
    {
        return $this->buildTrendFromDailyRows($period, 'resolutions_count');
    }

    // ═══════════════════════════════════════════════════════
    // PRIVATE
    // ═══════════════════════════════════════════════════════

    private function summaryFromRow(SupportDailyStat $row): array
    {
        return [
            'conversations_count'     => $row->conversations_count,
            'resolutions_count'       => $row->resolutions_count,
            'incoming_messages_count' => $row->incoming_messages_count,
            'outgoing_messages_count' => $row->outgoing_messages_count,
            'avg_first_response_time' => $row->avg_first_response_time,
            'avg_resolution_time'     => $row->avg_resolution_time,
        ];
    }

    private function safeAccountSummary(string $since, string $until): array
    {
        try {
            return $this->client->getAccountSummary($since, $until);
        } catch (\Exception $e) {
            Log::warning('[ReportService] Account summary unavailable', ['error' => $e->getMessage()]);
            return [
                'conversations_count'     => 0,
                'incoming_messages_count' => 0,
                'outgoing_messages_count' => 0,
                'avg_first_response_time' => 0,
                'avg_resolution_time'     => 0,
                'resolutions_count'       => 0,
            ];
        }
    }

    private function safeReport(string $metric, string $since, string $until): array
    {
        try {
            return $this->client->getAccountReport($metric, $since, $until);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function safeCounts(): array
    {
        try {
            return $this->client->getConversationCounts();
        } catch (\Exception $e) {
            return ['all_count' => 0, 'mine_count' => 0, 'unassigned_count' => 0,
                    'assigned_count' => 0, 'open_count' => 0, 'pending_count' => 0, 'resolved_count' => 0];
        }
    }

    public function resolvePeriod(string $period): array
    {
        return match ($period) {
            'today'   => [(string) Carbon::today()->timestamp,                   (string) Carbon::now()->timestamp],
            'week'    => [(string) Carbon::now()->startOfWeek()->timestamp,      (string) Carbon::now()->timestamp],
            'month'   => [(string) Carbon::now()->startOfMonth()->timestamp,     (string) Carbon::now()->timestamp],
            'quarter' => [(string) Carbon::now()->firstOfQuarter()->timestamp,   (string) Carbon::now()->timestamp],
            default   => [(string) Carbon::today()->timestamp,                   (string) Carbon::now()->timestamp],
        };
    }

    private function resolvePeriodTimestamps(string $period): array
    {
        [$since, $until] = $this->resolvePeriod($period);
        return ['since' => $since, 'until' => $until];
    }
}
