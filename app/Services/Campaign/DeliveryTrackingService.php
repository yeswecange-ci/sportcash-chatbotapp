<?php

namespace App\Services\Campaign;

use App\Models\CampaignMessage;
use App\Services\Twilio\TwilioService;
use Illuminate\Support\Facades\Log;

class DeliveryTrackingService
{
    public function __construct(
        private TwilioService $twilio,
    ) {}

    /**
     * Traiter un StatusCallback Twilio (webhook)
     */
    public function processStatusCallback(string $messageSid, string $status): void
    {
        $campaignMessage = CampaignMessage::where('twilio_message_sid', $messageSid)->first();

        if (!$campaignMessage) {
            Log::debug('StatusCallback pour un message non-campagne', ['sid' => $messageSid]);
            return;
        }

        $normalizedStatus = $this->normalizeStatus($status);

        $campaignMessage->update(['status' => $normalizedStatus]);

        Log::info('Statut message campagne mis à jour via webhook', [
            'sid'    => $messageSid,
            'status' => $normalizedStatus,
        ]);

        // Vérifier si tous les messages de la campagne sont terminés
        $this->checkCampaignCompletion($campaignMessage->campaign_id);
    }

    /**
     * Rafraîchir les statuts d'une campagne en interrogeant l'API Twilio
     */
    public function refreshCampaignStatuses(int $campaignId): array
    {
        $messages = CampaignMessage::where('campaign_id', $campaignId)
            ->whereNotNull('twilio_message_sid')
            ->whereNotIn('status', ['delivered', 'read', 'failed', 'undelivered'])
            ->get();

        $updated = 0;
        $errors = 0;

        foreach ($messages as $message) {
            try {
                $twilioMessage = $this->twilio->fetchMessage($message->twilio_message_sid);
                $newStatus = $this->normalizeStatus($twilioMessage->status);

                if ($newStatus !== $message->status) {
                    $message->update(['status' => $newStatus]);
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors++;
                Log::warning('Erreur polling statut Twilio', [
                    'sid'   => $message->twilio_message_sid,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->checkCampaignCompletion($campaignId);

        return compact('updated', 'errors');
    }

    /**
     * Normaliser le statut Twilio vers nos statuts internes
     */
    private function normalizeStatus(string $twilioStatus): string
    {
        return match (strtolower($twilioStatus)) {
            'queued', 'accepted'   => 'queued',
            'sending', 'sent'      => 'sent',
            'delivered'            => 'delivered',
            'read'                 => 'read',
            'failed', 'canceled'   => 'failed',
            'undelivered'          => 'undelivered',
            default                => 'queued',
        };
    }

    /**
     * Vérifie si tous les messages d'une campagne sont dans un état final
     * et marque la campagne comme "completed" si c'est le cas
     */
    private function checkCampaignCompletion(int $campaignId): void
    {
        $pending = CampaignMessage::where('campaign_id', $campaignId)
            ->whereIn('status', ['queued', 'sent'])
            ->exists();

        if (!$pending) {
            \App\Models\Campaign::where('id', $campaignId)
                ->where('status', 'active')
                ->update([
                    'status' => 'completed',
                    'sent_at' => now(),
                ]);
        }
    }
}
