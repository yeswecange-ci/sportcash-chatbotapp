<?php

namespace App\Console\Commands;

use App\Models\SupportAgentStat;
use App\Models\SupportDailyStat;
use App\Services\Chatwoot\ChatwootClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSupportStats extends Command
{
    protected $signature = 'stats:sync
                            {--period=all : Periode a synchroniser (today|week|month|quarter|all)}';

    protected $description = 'Synchronise les stats support client depuis Chatwoot vers la base de donnees locale';

    public function __construct(private ChatwootClient $client)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('[stats:sync] Demarrage synchronisation');

        // 1. Compteurs live (open / pending / resolved)
        $counts = $this->fetchCounts();
        $this->line("  → Compteurs live : open={$counts['open_count']}, pending={$counts['pending_count']}, resolved={$counts['resolved_count']}");

        // 2. Verifier disponibilite API Reports
        $reportsAvailable = $this->checkReportsApi();
        if (!$reportsAvailable) {
            $this->warn('  ~ API Reports non disponible — compteurs conversation uniquement');
        }

        // 3. Stats agents depuis l'API conversations (fonctionne toujours)
        $agentStatsFromConv = $this->buildAgentStatsFromConversations();
        $this->line("  → Stats agents (conversations) : " . count($agentStatsFromConv) . " agent(s) detecte(s)");

        // 4. Snapshot journalier (pour les courbes)
        $this->syncDailySnapshot($counts, $reportsAvailable);

        // 5. Resumes par periode + stats agents
        foreach (['today', 'week', 'month', 'quarter'] as $p) {
            $this->syncPeriodSummary($p, $counts, $reportsAvailable, $agentStatsFromConv);
        }

        $this->info('[stats:sync] Synchronisation terminee.');
        return self::SUCCESS;
    }

    // ─── Snapshot journalier ──────────────────────────────────────

    private function syncDailySnapshot(array $counts, bool $reportsAvailable): void
    {
        $today = Carbon::today()->toDateString();

        $data = [
            'conversations_count'     => $counts['open_count'] + $counts['pending_count'],
            'resolutions_count'       => $counts['resolved_count'],
            'incoming_messages_count' => 0,
            'outgoing_messages_count' => 0,
            'avg_first_response_time' => 0,
            'avg_resolution_time'     => 0,
            'open_count'              => $counts['open_count'],
            'pending_count'           => $counts['pending_count'],
            'resolved_count'          => $counts['resolved_count'],
            'trend_data'              => null,
        ];

        if ($reportsAvailable) {
            [$since, $until] = [$this->ts(Carbon::today()), $this->ts(Carbon::now())];
            try {
                $summary = $this->client->getAccountSummary($since, $until);
                $data['conversations_count']     = $summary['conversations_count'] ?? $data['conversations_count'];
                $data['resolutions_count']       = $summary['resolutions_count'] ?? $data['resolutions_count'];
                $data['incoming_messages_count'] = $summary['incoming_messages_count'] ?? 0;
                $data['outgoing_messages_count'] = $summary['outgoing_messages_count'] ?? 0;
                $data['avg_first_response_time'] = (int) ($summary['avg_first_response_time'] ?? 0);
                $data['avg_resolution_time']     = (int) ($summary['avg_resolution_time'] ?? 0);
            } catch (\Exception $e) {
                // garde valeurs live
            }
        }

        SupportDailyStat::upsertForPeriod($today, 'day', $data);
        $this->line("  ✓ Snapshot journalier [{$today}] : conv={$data['conversations_count']}, res={$data['resolutions_count']}");
    }

    // ─── Resume par periode ───────────────────────────────────────

    private function syncPeriodSummary(string $period, array $counts, bool $reportsAvailable, array $agentStats): void
    {
        $today = Carbon::today()->toDateString();

        $data = [
            'conversations_count'     => $counts['open_count'] + $counts['pending_count'] + $counts['resolved_count'],
            'resolutions_count'       => $counts['resolved_count'],
            'incoming_messages_count' => 0,
            'outgoing_messages_count' => 0,
            'avg_first_response_time' => 0,
            'avg_resolution_time'     => 0,
            'open_count'              => $counts['open_count'],
            'pending_count'           => $counts['pending_count'],
            'resolved_count'          => $counts['resolved_count'],
            'trend_data'              => null,
        ];

        if ($reportsAvailable) {
            [$since, $until] = $this->resolvePeriod($period);
            try {
                $summary = $this->client->getAccountSummary($since, $until);
                $data['conversations_count']     = $summary['conversations_count'] ?? $data['conversations_count'];
                $data['resolutions_count']       = $summary['resolutions_count'] ?? $data['resolutions_count'];
                $data['incoming_messages_count'] = $summary['incoming_messages_count'] ?? 0;
                $data['outgoing_messages_count'] = $summary['outgoing_messages_count'] ?? 0;
                $data['avg_first_response_time'] = (int) ($summary['avg_first_response_time'] ?? 0);
                $data['avg_resolution_time']     = (int) ($summary['avg_resolution_time'] ?? 0);
            } catch (\Exception $e) {
                // garde valeurs live
            }

            // Agent stats API (si disponible)
            try {
                $agentSummary = $this->client->getAgentSummary(...$this->resolvePeriod($period));
                $agentMap     = $this->fetchAgentsMap();
                $this->storeAgentStats($agentSummary, $agentMap, $today, $period);
            } catch (\Exception $e) {
                // Agent stats depuis conversations à la place
                $this->storeAgentStatsFromCounts($agentStats, $today, $period);
            }
        } else {
            // Toujours stocker les stats agents depuis les conversations
            $this->storeAgentStatsFromCounts($agentStats, $today, $period);
        }

        SupportDailyStat::upsertForPeriod($today, $period, $data);
        $this->line("  ✓ Resume [{$period}] : conv={$data['conversations_count']}, res={$data['resolutions_count']}");
    }

    // ─── Stats agents depuis API conversations ────────────────────

    /**
     * Construit des stats agents en paginant les conversations ouvertes et résolues.
     * Retourne un tableau indexé par agent_id.
     */
    private function buildAgentStatsFromConversations(): array
    {
        $agentCounts = []; // [agent_id => ['name'=>, 'email'=>, 'open'=>, 'resolved'=>]]

        // Conversations ouvertes (max 5 pages = 500 conversations)
        foreach (['open', 'resolved'] as $status) {
            $maxPages = $status === 'open' ? 5 : 5;
            for ($page = 1; $page <= $maxPages; $page++) {
                try {
                    $response = $this->client->listConversations($status, 'all', $page);
                    $convs    = $response['data']['payload'] ?? [];

                    if (empty($convs)) break;

                    foreach ($convs as $conv) {
                        $assignee = $conv['meta']['assignee'] ?? null;
                        if (!$assignee || empty($assignee['id'])) continue;

                        $aid = (int) $assignee['id'];
                        if (!isset($agentCounts[$aid])) {
                            $agentCounts[$aid] = [
                                'name'      => $assignee['name'] ?? ('Agent #' . $aid),
                                'email'     => $assignee['email'] ?? null,
                                'open'      => 0,
                                'resolved'  => 0,
                            ];
                        }
                        $agentCounts[$aid][$status === 'open' ? 'open' : 'resolved']++;
                    }

                    // Derniere page atteinte
                    $meta    = $response['data']['meta'] ?? [];
                    $allCount = $meta['all_count'] ?? count($convs);
                    if ($page * 25 >= $allCount) break;

                } catch (\Exception $e) {
                    break;
                }
            }
        }

        return $agentCounts;
    }

    private function storeAgentStatsFromCounts(array $agentStats, string $date, string $period): void
    {
        foreach ($agentStats as $agentId => $data) {
            SupportAgentStat::upsertForAgent($date, $period, $agentId, [
                'agent_name'              => $data['name'],
                'agent_email'             => $data['email'],
                'conversations_count'     => ($data['open'] ?? 0) + ($data['resolved'] ?? 0),
                'resolutions_count'       => $data['resolved'] ?? 0,
                'avg_first_response_time' => 0,
                'avg_resolution_time'     => 0,
            ]);
        }
    }

    private function storeAgentStats(array $agentSummary, array $agentsMap, string $date, string $period): void
    {
        $entries = $agentSummary['data'] ?? $agentSummary;
        if (empty($entries)) return;

        foreach ($entries as $entry) {
            $agentId = $entry['id'] ?? null;
            if (!$agentId) continue;
            $metric    = $entry['metric'] ?? $entry;
            $agentInfo = $agentsMap[$agentId] ?? null;

            SupportAgentStat::upsertForAgent($date, $period, (int) $agentId, [
                'agent_name'              => $agentInfo['name'] ?? ('Agent #' . $agentId),
                'agent_email'             => $agentInfo['email'] ?? null,
                'conversations_count'     => (int) ($metric['conversations_count'] ?? 0),
                'resolutions_count'       => (int) ($metric['resolutions_count'] ?? 0),
                'avg_first_response_time' => (int) ($metric['avg_first_response_time'] ?? 0),
                'avg_resolution_time'     => (int) ($metric['avg_resolution_time'] ?? 0),
            ]);
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function fetchCounts(): array
    {
        try {
            return $this->client->getConversationCounts();
        } catch (\Exception $e) {
            return ['open_count' => 0, 'pending_count' => 0, 'resolved_count' => 0,
                    'all_count' => 0, 'mine_count' => 0, 'unassigned_count' => 0, 'assigned_count' => 0];
        }
    }

    private function checkReportsApi(): bool
    {
        try {
            [$since, $until] = [$this->ts(Carbon::today()), $this->ts(Carbon::now())];
            $this->client->getAccountSummary($since, $until);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function fetchAgentsMap(): array
    {
        try {
            return collect($this->client->listAgents())->keyBy('id')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function resolvePeriod(string $period): array
    {
        return match ($period) {
            'today'   => [$this->ts(Carbon::today()),                  $this->ts(Carbon::now())],
            'week'    => [$this->ts(Carbon::now()->startOfWeek()),     $this->ts(Carbon::now())],
            'month'   => [$this->ts(Carbon::now()->startOfMonth()),    $this->ts(Carbon::now())],
            'quarter' => [$this->ts(Carbon::now()->firstOfQuarter()),  $this->ts(Carbon::now())],
            default   => [$this->ts(Carbon::today()),                  $this->ts(Carbon::now())],
        };
    }

    private function ts(Carbon $dt): string
    {
        return (string) $dt->timestamp;
    }
}
