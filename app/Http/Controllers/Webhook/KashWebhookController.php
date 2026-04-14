<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Kash\KashSignalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Reçoit les signaux du bot Kash (n8n) :
 *   POST /api/reclamations  ← signal [[RECLAMATION]]
 *   POST /api/escalades     ← signal [[ESCALADE]]
 *
 * Sécurisation : header "X-Bot-Secret: {BOT_WEBHOOK_SECRET}"
 */
class KashWebhookController extends Controller
{
    public function __construct(
        private KashSignalService $kash
    ) {}

    // ── POST /api/reclamations ────────────────────────────────────────────────

    public function reclamation(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();

        Log::info('[KASH WEBHOOK] Signal RECLAMATION reçu', $payload);

        try {
            $reclamation = $this->kash->handleReclamation(array_merge($payload, [
                'sender' => $payload['sender'] ?? $payload['identifiant'] ?? '',
            ]));

            return response()->json([
                'success'   => true,
                'reference' => $reclamation->reference,
                'autoRef'   => $reclamation->reference,
                'id'        => $reclamation->id,
            ], 201);

        } catch (\Exception $e) {
            Log::error('[KASH WEBHOOK] Erreur RECLAMATION', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── POST /api/escalades ───────────────────────────────────────────────────

    public function escalade(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();

        Log::info('[KASH WEBHOOK] Signal ESCALADE reçu', $payload);

        try {
            $escalade = $this->kash->handleEscalade(array_merge($payload, [
                'sender' => $payload['sender'] ?? $payload['identifiant'] ?? '',
            ]));

            return response()->json([
                'success'   => true,
                'reference' => $escalade->reference,
                'autoRef'   => $escalade->reference,
                'id'        => $escalade->id,
                'chatwoot_conversation_id' => $escalade->chatwoot_conversation_id,
            ], 201);

        } catch (\Exception $e) {
            Log::error('[KASH WEBHOOK] Erreur ESCALADE', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Auth ─────────────────────────────────────────────────────────────────

    private function isAuthorized(Request $request): bool
    {
        $secret = config('app.bot_webhook_secret');

        // Si pas de secret configuré, on accepte (dev)
        if (!$secret) {
            return true;
        }

        return $request->header('X-Bot-Secret') === $secret;
    }
}
