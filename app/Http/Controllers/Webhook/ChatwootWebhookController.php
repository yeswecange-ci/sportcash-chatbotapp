<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Kash\KashSignalService;
use App\Services\Twilio\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ChatwootWebhookController extends Controller
{
    public function __construct(
        private TwilioService $twilio,
        private KashSignalService $kash,
    ) {}

    /**
     * Webhook Chatwoot → Laravel → Twilio
     *
     * Configuration dans Chatwoot :
     * Settings → Integrations → Configure → Webhooks
     * URL : https://votre-app.com/api/webhooks/chatwoot
     * Événements à cocher :
     *   ✅ message_created
     *   ✅ conversation_status_changed
     */
    public function handle(Request $request): JsonResponse
    {
        $event = $request->input('event');

        Log::info('[CHATWOOT WEBHOOK]', ['event' => $event]);

        return match ($event) {
            'message_created'             => $this->onMessageCreated($request),
            'conversation_status_changed' => $this->onStatusChanged($request),
            default                       => response()->json(['ok' => true]),
        };
    }

    /**
     * Un agent a envoyé un message → le transmettre au client WhatsApp
     */
    private function onMessageCreated(Request $request): JsonResponse
    {
        $messageType = $request->input('message_type');
        $private     = $request->boolean('private', false);
        $content     = $request->input('content', '');

        // Ne transmettre que les messages SORTANTS et NON privés
        if ($messageType !== 'outgoing' || $private) {
            return response()->json(['ok' => true, 'action' => 'skipped']);
        }

        // Extraire les URLs des pièces jointes (messages media)
        $attachments = $request->input('attachments', []);
        $mediaUrls   = collect($attachments)->pluck('data_url')->filter()->values()->all();

        // Ignorer si ni texte ni media
        if (empty($content) && empty($mediaUrls)) {
            return response()->json(['ok' => true, 'action' => 'skipped']);
        }

        // Récupérer le numéro du client
        $phone = $this->extractClientPhone($request);

        if (!$phone) {
            Log::warning('[CHATWOOT WEBHOOK] Pas de numéro client trouvé', [
                'conversation_id' => $request->input('conversation.id'),
            ]);
            return response()->json(['ok' => true, 'action' => 'no_phone']);
        }

        // Envoyer via Twilio WhatsApp (texte et/ou media)
        try {
            $this->twilio->sendWhatsApp($phone, $content, $mediaUrls);

            Log::info('[CHATWOOT WEBHOOK] Message transmis au client', [
                'phone'       => $phone,
                'content'     => mb_substr($content, 0, 50),
                'media_count' => count($mediaUrls),
            ]);

            return response()->json(['ok' => true, 'action' => 'sent']);

        } catch (\Exception $e) {
            Log::error('[CHATWOOT WEBHOOK] Erreur envoi Twilio', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Conversation résolue → notifier le client + synchroniser escalade Kash
     */
    private function onStatusChanged(Request $request): JsonResponse
    {
        $newStatus      = $request->input('status');
        $phone          = $this->extractClientPhone($request);
        $conversationId = $request->input('conversation.id') ?? $request->input('id');

        // Synchroniser l'escalade Kash liée à cette conversation Chatwoot
        if ($conversationId) {
            try {
                $this->kash->syncEscaladeFromChatwoot((int) $conversationId, $newStatus);
            } catch (\Exception $e) {
                Log::warning('[CHATWOOT WEBHOOK] Sync escalade Kash échouée', [
                    'conversation_id' => $conversationId,
                    'error'           => $e->getMessage(),
                ]);
            }
        }

        if ($newStatus === 'resolved' && $phone) {
            try {
                $this->twilio->sendWhatsApp(
                    $phone,
                    "✅ Merci de nous avoir contacté ! Votre demande a été traitée. N'hésitez pas à nous écrire si vous avez d'autres questions."
                );
            } catch (\Exception $e) {
                Log::error('[CHATWOOT WEBHOOK] Erreur notification résolution', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Extraire le numéro de téléphone du client depuis le payload webhook
     */
    private function extractClientPhone(Request $request): ?string
    {
        // Essayer plusieurs chemins possibles dans le payload
        return $request->input('conversation.contact_inbox.source_id')
            ?? $request->input('conversation.meta.sender.phone_number')
            ?? $request->input('meta.sender.phone_number')
            ?? $request->input('sender.phone_number');
    }
}
