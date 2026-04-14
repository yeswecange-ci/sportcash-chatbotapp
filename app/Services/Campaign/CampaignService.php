<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Models\Contact;
use App\Jobs\SendCampaignMessages;
use Illuminate\Support\Facades\Auth;

class CampaignService
{
    public function create(array $data): Campaign
    {
        $data['created_by'] = Auth::id();
        $data['status'] = 'draft';

        return Campaign::create($data);
    }

    public function update(Campaign $campaign, array $data): Campaign
    {
        $campaign->update($data);
        return $campaign->fresh();
    }

    public function delete(Campaign $campaign): void
    {
        $campaign->messages()->delete();
        $campaign->contacts()->detach();
        $campaign->delete();
    }

    public function attachContacts(Campaign $campaign, array $contactIds): void
    {
        $campaign->contacts()->syncWithoutDetaching($contactIds);
    }

    public function detachContacts(Campaign $campaign, array $contactIds): void
    {
        $campaign->contacts()->detach($contactIds);
    }

    /**
     * Lancer l'envoi immédiat de la campagne
     */
    public function sendNow(Campaign $campaign): void
    {
        $campaign->update(['status' => 'active']);
        SendCampaignMessages::dispatch($campaign, Auth::id());
    }

    /**
     * Planifier l'envoi de la campagne
     * Le statut passe à 'scheduled' ; la commande console campaigns:send-scheduled
     * se chargera de déclencher l'envoi à la bonne heure.
     */
    public function schedule(Campaign $campaign, string $scheduledAt): void
    {
        $campaign->update([
            'status'       => 'scheduled',
            'scheduled_at' => \Illuminate\Support\Carbon::parse($scheduledAt),
        ]);
    }

    /**
     * Annuler une planification (repasse en brouillon)
     */
    public function cancelSchedule(Campaign $campaign): void
    {
        $campaign->update([
            'status'       => 'draft',
            'scheduled_at' => null,
        ]);
    }

    /**
     * Envoyer un message template à un contact unique dans une campagne
     */
    public function sendSingle(Campaign $campaign, Contact $contact): void
    {
        $message = \App\Models\CampaignMessage::create([
            'campaign_id'  => $campaign->id,
            'contact_id'   => $contact->id,
            'template_sid' => $campaign->template_sid,
            'status'       => 'queued',
            'sent_by'      => Auth::id(),
        ]);

        \App\Jobs\SendSingleCampaignMessage::dispatchSync($message);
    }

    /**
     * Statistiques de la campagne
     */
    public function stats(Campaign $campaign): array
    {
        $messages = $campaign->messages();

        return [
            'total'       => $messages->count(),
            'queued'      => (clone $messages)->where('status', 'queued')->count(),
            'sent'        => (clone $messages)->where('status', 'sent')->count(),
            'delivered'   => (clone $messages)->where('status', 'delivered')->count(),
            'read'        => (clone $messages)->where('status', 'read')->count(),
            'failed'      => (clone $messages)->where('status', 'failed')->count(),
            'undelivered' => (clone $messages)->where('status', 'undelivered')->count(),
        ];
    }
}
