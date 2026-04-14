<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\Campaign\DeliveryTrackingService;
use App\Services\Chatwoot\ChatwootClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TwilioWebhookController extends Controller
{
    public function __construct(
        private ChatwootClient $chatwoot
    ) {}

    /**
     * HANDOFF : Twilio Studio → Chatwoot
     *
     * Configuration dans Twilio Studio :
     * Widget "Make HTTP Request" (POST)
     * URL : https://votre-app.com/api/webhooks/twilio/handoff
     * Content-Type : application/json
     * Body :
     * {
     *   "from": "{{trigger.message.From}}",
     *   "body": "{{trigger.message.Body}}",
     *   "name": "{{flow.variables.customer_name}}"
     * }
     */
    public function handoff(Request $request): JsonResponse
    {
        $from = $request->input('from');        // whatsapp:+2250700000000
        $body = $request->input('body', '');
        $name = $request->input('name', 'Client WhatsApp');

        Log::info('[HANDOFF] Twilio → Chatwoot', compact('from', 'body', 'name'));

        try {
            // ── 1. Normaliser le numéro ──────────────────
            $phone = str_replace('whatsapp:', '', $from);

            // ── 2. Chercher ou créer le contact ──────────
            $contact = $this->findOrCreateContact($phone, $name);
            $contactId = $contact['id'];
            $sourceId  = $this->getSourceId($contact, $from);

            // ── 3. Créer la conversation ─────────────────
            $conversation = $this->chatwoot->createConversation(
                sourceId: $sourceId,
                inboxId: (int) config('chatwoot.whatsapp_inbox_id'),
                contactId: $contactId,
                initialMessage: $body ?: "📞 Le client a demandé à parler à un agent."
            );

            $conversationId = $conversation['id'] ?? $conversation['conversation_id'] ?? null;

            // Auto-save contact local pour les campagnes
            $this->autoSaveLocalContact($phone, $name, $contactId);

            Log::info('[HANDOFF] Conversation créée', [
                'conversation_id' => $conversationId,
                'contact_id'      => $contactId,
            ]);

            return response()->json([
                'success'         => true,
                'conversation_id' => $conversationId,
                'contact_id'      => $contactId,
            ]);

        } catch (\Exception $e) {
            Log::error('[HANDOFF] Erreur', [
                'from'  => $from,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * StatusCallback : Twilio notifie le statut d'un message campagne
     */
    public function statusCallback(Request $request): Response
    {
        $messageSid = $request->input('MessageSid');
        $status = $request->input('MessageStatus');

        Log::info('[STATUS CALLBACK] Twilio', compact('messageSid', 'status'));

        if ($messageSid && $status) {
            try {
                app(DeliveryTrackingService::class)->processStatusCallback($messageSid, $status);
            } catch (\Exception $e) {
                Log::error('[STATUS CALLBACK] Erreur', ['error' => $e->getMessage()]);
            }
        }

        return response('', 204);
    }

    /**
     * Chercher le contact par téléphone, ou le créer
     */
    private function findOrCreateContact(string $phone, string $name): array
    {
        $search = $this->chatwoot->searchContacts($phone);
        $contacts = $search['payload'] ?? [];

        if (count($contacts) > 0) {
            Log::info('[HANDOFF] Contact existant trouvé', ['id' => $contacts[0]['id']]);
            return $contacts[0];
        }

        Log::info('[HANDOFF] Création nouveau contact', compact('phone', 'name'));

        $result = $this->chatwoot->createContact(
            name: $name,
            phoneNumber: $phone,
        );

        return $result['payload']['contact'];
    }

    /**
     * Sauvegarder le contact localement pour les campagnes
     */
    private function autoSaveLocalContact(string $phone, string $name, int $chatwootContactId): void
    {
        try {
            Contact::firstOrCreate(
                ['phone_number' => $phone],
                [
                    'name' => $name,
                    'chatwoot_contact_id' => $chatwootContactId,
                ]
            );
        } catch (\Exception $e) {
            Log::warning('[HANDOFF] Auto-save contact local échoué', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Extraire le source_id du contact (nécessaire pour créer la conversation)
     */
    private function getSourceId(array $contact, string $fallback): string
    {
        $inboxes = $contact['contact_inboxes'] ?? [];

        foreach ($inboxes as $inbox) {
            if (!empty($inbox['source_id'])) {
                return $inbox['source_id'];
            }
        }

        // Fallback : utiliser le numéro WhatsApp
        return $fallback;
    }
}
