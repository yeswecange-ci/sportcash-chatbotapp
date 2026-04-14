<?php

namespace App\Observers;

use App\Models\Client;
use App\Models\Conversation;
use App\Models\DailyStatistic;
use Illuminate\Support\Facades\Log;

class ClientObserver
{
    /**
     * Déclenché après chaque mise à jour d'un client.
     *
     * Si is_client a changé, on propage la nouvelle valeur vers toutes les
     * conversations du client et on recalcule les stats quotidiennes impactées,
     * afin que tous les dashboards et vues restent cohérents.
     */
    public function updated(Client $client): void
    {
        if (!$client->wasChanged('is_client')) {
            return;
        }

        $phone = $client->phone_number;
        $newValue = $client->is_client;

        Log::info('ClientObserver: is_client changed', [
            'phone'     => $phone,
            'old_value' => $client->getOriginal('is_client'),
            'new_value' => $newValue,
        ]);

        // 1. Propager la valeur vers toutes les conversations du client
        Conversation::where('phone_number', $phone)
            ->update(['is_client' => $newValue]);

        // 2. Recalculer les stats quotidiennes pour chaque date impactée
        $affectedDates = Conversation::where('phone_number', $phone)
            ->whereNotNull('started_at')
            ->selectRaw('DATE(started_at) as date')
            ->distinct()
            ->pluck('date');

        foreach ($affectedDates as $date) {
            DailyStatistic::recalculateForDate(new \DateTime($date));
        }

        Log::info('ClientObserver: propagation terminée', [
            'phone'          => $phone,
            'conversations'  => Conversation::where('phone_number', $phone)->count(),
            'dates_impacted' => $affectedDates->count(),
        ]);
    }
}
