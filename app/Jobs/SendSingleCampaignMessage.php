<?php

namespace App\Jobs;

use App\Models\CampaignMessage;
use App\Services\Twilio\TwilioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSingleCampaignMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public CampaignMessage $campaignMessage,
    ) {}

    public function handle(TwilioService $twilio): void
    {
        $message = $this->campaignMessage;
        $campaign = $message->campaign;
        $contact = $message->contact;

        if (!$contact || !$campaign) {
            $message->update([
                'status'        => 'failed',
                'error_message' => 'Contact ou campagne introuvable',
            ]);
            return;
        }

        try {
            // StatusCallback uniquement si APP_URL est une URL publique (pas localhost)
            $appUrl = config('app.url');
            $statusCallbackUrl = null;
            if ($appUrl && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
                $statusCallbackUrl = $appUrl . '/api/twilio/status';
            }

            $twilioMessage = $twilio->sendTemplateWithCallback(
                $contact->phone_number,
                $campaign->template_sid,
                $campaign->template_variables ?? [],
                $statusCallbackUrl,
            );

            $message->update([
                'twilio_message_sid' => $twilioMessage->sid,
                'status'             => 'sent',
                'sent_at'            => now(),
            ]);

            Log::info('Message campagne envoyé', [
                'campaign'   => $campaign->id,
                'contact'    => $contact->id,
                'twilio_sid' => $twilioMessage->sid,
            ]);
        } catch (\Exception $e) {
            $message->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at'       => now(),
            ]);

            Log::error('Échec envoi message campagne', [
                'campaign' => $campaign->id,
                'contact'  => $contact->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
