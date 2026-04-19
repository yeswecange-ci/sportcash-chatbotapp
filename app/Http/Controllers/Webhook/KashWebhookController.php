<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\BotEscalade;
use App\Models\BotReclamation;
use App\Models\KashBotMessage;
use App\Services\Chatwoot\ChatwootClient;
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

    // ── GET /api/kash/escalade-active?sender=X ───────────────────────────────

    public function checkEscalade(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sender = $request->get('sender', '');

        $active = BotEscalade::where('sender', $sender)
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->exists();

        return response()->json(['active' => $active]);
    }

    // ── GET /api/kash/reclamation-active?sender=X ────────────────────────────

    public function checkReclamation(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sender = $request->get('sender', '');

        $active = BotReclamation::where('sender', $sender)
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->exists();

        return response()->json(['active' => $active]);
    }

    // ── GET /api/kash/bot-actif?sender=X ─────────────────────────────────────
    //
    // Logique :
    //   - Si un ticket (escalade ou réclamation) est ouvert (en_attente|en_cours)
    //     → bot_actif=false  : le bot se tait, les messages partent vers Chatwoot
    //   - Si aucun ticket ouvert (pas de ticket, ou tous résolus/fermés)
    //     → bot_actif=true   : le bot reprend la main

    public function checkBotActif(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sender = $request->get('sender', '');

        $ticketOuvert = $this->kash->hasActiveTicket($sender);

        return response()->json([
            'bot_actif' => !$ticketOuvert,
            'raison'    => $ticketOuvert ? 'ticket_ouvert' : 'aucun_ticket_ouvert',
        ]);
    }

    // ── POST /api/kash/messages ───────────────────────────────────────────────

    public function logMessage(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        KashBotMessage::create([
            'sender'      => $request->input('sender', ''),
            'direction'   => $request->input('direction', 'inbound'),
            'content'     => $request->input('content', ''),
            'signal_type' => $request->input('signal_type', 'none'),
        ]);

        return response()->json(['ok' => true], 201);
    }

    // ── POST /api/kash/forward-to-support ────────────────────────────────────

    public function forwardToSupport(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sender  = $request->input('sender', '');
        $message = $request->input('message', '');

        // Chercher un ticket actif : escalade ou réclamation
        $ticket = BotEscalade::where('sender', $sender)
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->latest()
            ->first()
            ?? BotReclamation::where('sender', $sender)
                ->whereIn('statut', ['en_attente', 'en_cours'])
                ->latest()
                ->first();

        if (!$ticket) {
            Log::info('[KASH] forwardToSupport: aucun ticket actif', ['sender' => $sender]);
            return response()->json(['ok' => false, 'reason' => 'no_active_ticket'], 200);
        }

        if (!$ticket->chatwoot_conversation_id) {
            Log::warning('[KASH] forwardToSupport: ticket sans chatwoot_conversation_id', [
                'reference' => $ticket->reference,
                'sender'    => $sender,
            ]);
            return response()->json(['ok' => false, 'reason' => 'no_chatwoot_conversation'], 200);
        }

        try {
            $chatwoot = new ChatwootClient();
            $convId   = $ticket->chatwoot_conversation_id;

            try {
                $chatwoot->createIncomingMessage($convId, $message);
            } catch (\Exception $inner) {
                Log::warning('[KASH] createIncomingMessage échoué, fallback note privée', [
                    'error' => $inner->getMessage(),
                ]);
                $phone = str_replace('whatsapp:', '', $sender);
                $chatwoot->sendMessage(
                    conversationId: $convId,
                    content:        "📱 *{$phone} :* {$message}",
                    isPrivate:      true,
                );
            }

            Log::info('[KASH] Message client transféré vers Chatwoot', [
                'sender'                   => $sender,
                'reference'                => $ticket->reference,
                'chatwoot_conversation_id' => $convId,
            ]);

            return response()->json(['ok' => true, 'chatwoot_conversation_id' => $convId]);

        } catch (\Exception $e) {
            Log::error('[KASH] Erreur forward vers Chatwoot', [
                'sender' => $sender,
                'error'  => $e->getMessage(),
            ]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ── GET /api/kash/client-profil?sender=X ─────────────────────────────────
    //
    // Retourne l'identifiant connu du client (depuis ses tickets précédents)
    // pour permettre à l'IA de ne pas le redemander.

    public function clientProfil(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sender = $request->get('sender', '');

        $lastTicket = BotReclamation::where('sender', $sender)
            ->whereNotNull('identifiant')
            ->where('identifiant', '!=', '')
            ->latest()
            ->first()
            ?? BotEscalade::where('sender', $sender)
                ->whereNotNull('identifiant')
                ->where('identifiant', '!=', '')
                ->latest()
                ->first();

        $nbTickets = BotReclamation::where('sender', $sender)->count()
                   + BotEscalade::where('sender', $sender)->count();

        return response()->json([
            'found'       => !is_null($lastTicket),
            'identifiant' => $lastTicket?->identifiant,
            'nb_tickets'  => $nbTickets,
        ]);
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
