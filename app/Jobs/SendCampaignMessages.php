<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public int $sentBy,
    ) {}

    public function handle(): void
    {
        $contacts = $this->campaign->contacts;

        if ($contacts->isEmpty()) {
            Log::warning('Campagne sans contacts', ['campaign' => $this->campaign->id]);
            return;
        }

        $delay = 0;

        foreach ($contacts as $contact) {
            // Créer l'enregistrement CampaignMessage
            $message = CampaignMessage::create([
                'campaign_id'  => $this->campaign->id,
                'contact_id'   => $contact->id,
                'template_sid' => $this->campaign->template_sid,
                'status'       => 'queued',
                'sent_by'      => $this->sentBy,
            ]);

            // Dispatcher l'envoi individuel avec un délai progressif (1s entre chaque)
            SendSingleCampaignMessage::dispatch($message)
                ->delay(now()->addSeconds($delay));

            $delay++;
        }

        $this->campaign->update([
            'status'  => 'active',
            'sent_at' => now(),
        ]);

        Log::info('Campagne orchestrée', [
            'campaign' => $this->campaign->id,
            'contacts' => $contacts->count(),
        ]);
    }
}
