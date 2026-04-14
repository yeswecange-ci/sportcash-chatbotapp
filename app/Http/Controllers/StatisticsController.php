<?php

namespace App\Http\Controllers;

use App\Services\Chatwoot\ReportService;
use App\Services\Chatwoot\ChatwootClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class StatisticsController extends Controller
{
    public function __construct(
        private ReportService $reports,
        private ChatwootClient $chatwoot,
    ) {}

    /**
     * Page statistiques
     * GET /statistics
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'week');

        $stats           = $this->reports->getFullStats($period);
        $agentLeaderboard = $this->reports->getAgentLeaderboard($period);
        $lastSynced      = $this->reports->lastSyncedAt();

        try {
            $agents = $this->chatwoot->listAgents();
        } catch (\Exception $e) {
            $agents = [];
        }

        return view('statistics.index', compact(
            'stats', 'agents', 'agentLeaderboard', 'period', 'lastSynced'
        ))->with('currentPeriod', $period);
    }

    /**
     * AJAX — Donnees stats pour changement de periode dynamique
     * GET /ajax/statistics/data
     */
    public function data(Request $request): JsonResponse
    {
        $period = $request->get('period', 'week');

        try {
            $stats           = $this->reports->getFullStats($period);
            $agentLeaderboard = $this->reports->getAgentLeaderboard($period);
            $trends          = $this->reports->getConversationTrends($period);
            $lastSynced      = $this->reports->lastSyncedAt();

            return response()->json([
                'stats'      => $stats,
                'leaderboard' => $agentLeaderboard,
                'trends'     => $trends,
                'lastSynced' => $lastSynced?->toIso8601String(),
                'source'     => $stats['source'] ?? 'api',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Synchronisation manuelle
     * POST /ajax/statistics/sync
     */
    public function syncStats(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'all');
            Artisan::call('stats:sync', ['--period' => $period]);

            $lastSynced = $this->reports->lastSyncedAt();

            return response()->json([
                'success'    => true,
                'message'    => 'Synchronisation effectuee',
                'lastSynced' => $lastSynced?->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
