<?php

namespace App\Services\Kash;

use App\Models\BotEscalade;
use App\Models\BotReclamation;
use App\Models\Contact;
use App\Services\Chatwoot\ChatwootClient;
use Illuminate\Support\Facades\Log;

class KashSignalService
{
    public function __construct(
        private ChatwootClient $chatwoot
    ) {}

    // ── Réclamation ──────────────────────────────────────────────────────────

    /**
     * Traite un signal RECLAMATION envoyé par n8n.
     * Retourne la réclamation créée avec sa référence.
     */
    public function handleReclamation(array $payload): BotReclamation
    {
        $sender = $payload['sender'] ?? '';

        $reclamation = BotReclamation::create([
            'reference'        => $this->generateRef('REC', BotReclamation::class),
            'identifiant'      => $payload['identifiant'] ?? '',
            'sender'           => $sender,
            'canal'            => $payload['canal'] ?? null,
            'type_reclamation' => $payload['type'] ?? null,
            'infos'            => $payload['infos'] ?? null,
            'priorite'         => $payload['priorite'] ?? 'normale',
            'statut'           => 'en_attente',
        ]);

        Log::info('[KASH] Réclamation créée', [
            'reference'   => $reclamation->reference,
            'identifiant' => $reclamation->identifiant,
            'priorite'    => $reclamation->priorite,
        ]);

        return $reclamation;
    }

    // ── Escalade ─────────────────────────────────────────────────────────────

    /**
     * Traite un signal ESCALADE envoyé par n8n.
     * Crée la conversation Chatwoot (réutilise la logique handoff)
     * et persiste l'escalade en base.
     */
    public function handleEscalade(array $payload): BotEscalade
    {
        $sender = $payload['sender'] ?? '';

        $escalade = BotEscalade::create([
            'reference'   => $this->generateRef('ESC', BotEscalade::class),
            'identifiant' => $payload['identifiant'] ?? '',
            'sender'      => $sender,
            'raison'      => $payload['raison'] ?? null,
            'resume'      => $payload['resume'] ?? null,
            'statut'      => 'en_attente',
        ]);

        // Créer la conversation Chatwoot pour le transfert humain
        $chatwootId = $this->createChatwootConversation($escalade, $payload);

        if ($chatwootId) {
            $escalade->update(['chatwoot_conversation_id' => $chatwootId]);
        }

        Log::info('[KASH] Escalade créée', [
            'reference'               => $escalade->reference,
            'chatwoot_conversation_id' => $chatwootId,
        ]);

        return $escalade;
    }

    // ── Mise à jour statut via Chatwoot webhook ───────────────────────────────

    /**
     * Appelé par ChatwootWebhookController quand une conversation change de statut.
     * Met à jour l'escalade liée si elle existe.
     */
    public function syncEscaladeFromChatwoot(int $chatwootConversationId, string $chatwootStatus): void
    {
        $escalade = BotEscalade::where('chatwoot_conversation_id', $chatwootConversationId)->first();

        if (!$escalade) {
            return;
        }

        $newStatut = match ($chatwootStatus) {
            'resolved' => 'resolue',
            'open'     => 'en_cours',
            'pending'  => 'en_attente',
            default    => null,
        };

        if ($newStatut && $escalade->statut !== $newStatut) {
            $escalade->update(['statut' => $newStatut]);

            Log::info('[KASH] Escalade synchronisée depuis Chatwoot', [
                'reference'              => $escalade->reference,
                'chatwoot_conversation_id' => $chatwootConversationId,
                'nouveau_statut'         => $newStatut,
            ]);
        }
    }

    // ── Chatwoot handoff (réutilise la logique existante) ────────────────────

    private function createChatwootConversation(BotEscalade $escalade, array $payload): ?int
    {
        try {
            $phone = str_replace('whatsapp:', '', $escalade->sender);

            // Chercher ou créer le contact Chatwoot
            $contact   = $this->findOrCreateContact($phone, $escalade->identifiant);
            $contactId = $contact['id'];
            $sourceId  = $this->getSourceId($contact, $escalade->sender);

            // Sauvegarder le contact localement pour les campagnes
            Contact::firstOrCreate(
                ['phone_number' => $phone],
                ['name' => $escalade->identifiant, 'chatwoot_contact_id' => $contactId]
            );

            $initialMessage = implode("\n", array_filter([
                "🤖 *Transfert depuis Kash Bot* — {$escalade->reference}",
                "👤 Identifiant : {$escalade->identifiant}",
                $escalade->raison ? "⚠️ Raison : {$escalade->raison}" : null,
                $escalade->resume ? "📋 Résumé :\n{$escalade->resume}" : null,
            ]));

            $conversation = $this->chatwoot->createConversation(
                sourceId: $sourceId,
                inboxId: (int) config('chatwoot.whatsapp_inbox_id'),
                contactId: $contactId,
                initialMessage: $initialMessage,
            );

            return $conversation['id'] ?? $conversation['conversation_id'] ?? null;

        } catch (\Exception $e) {
            Log::error('[KASH] Erreur création conversation Chatwoot pour escalade', [
                'reference' => $escalade->reference,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function findOrCreateContact(string $phone, string $name): array
    {
        $search   = $this->chatwoot->searchContacts($phone);
        $contacts = $search['payload'] ?? [];

        if (count($contacts) > 0) {
            return $contacts[0];
        }

        $result = $this->chatwoot->createContact(name: $name, phoneNumber: $phone);
        return $result['payload']['contact'];
    }

    private function getSourceId(array $contact, string $fallback): string
    {
        foreach ($contact['contact_inboxes'] ?? [] as $inbox) {
            if (!empty($inbox['source_id'])) {
                return $inbox['source_id'];
            }
        }
        return $fallback;
    }

    // ── Génération référence ──────────────────────────────────────────────────

    private function generateRef(string $prefix, string $modelClass): string
    {
        $date  = now()->format('Ymd');
        $count = $modelClass::whereDate('created_at', today())->count() + 1;
        return "{$prefix}-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
