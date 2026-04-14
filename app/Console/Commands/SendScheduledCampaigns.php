<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignMessages;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledCampaigns extends Command
{
    protected $signature   = 'campaigns:send-scheduled';
    protected $description = 'Envoie les campagnes planifiées dont l\'heure est arrivée';

    public function handle(): void
    {
        $due = Campaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($due as $campaign) {
            if ($campaign->contacts()->count() === 0) {
                $campaign->update(['status' => 'draft', 'scheduled_at' => null]);
                Log::warning('Campagne planifiée sans contacts, annulation', ['campaign' => $campaign->id]);
                continue;
            }

            SendCampaignMessages::dispatch($campaign, $campaign->created_by ?? 0);

            Log::info('Campagne planifiée déclenchée', [
                'campaign'     => $campaign->id,
                'scheduled_at' => $campaign->scheduled_at,
            ]);
        }

        if ($due->isNotEmpty()) {
            $this->info("Campagnes déclenchées : {$due->count()}");
        }
    }
}
