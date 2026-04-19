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

        $initialMessage = implode("\n", array_filter([
            "🤖 *Réclamation depuis Kash Bot* — {$reclamation->reference}",
            "👤 Identifiant : {$reclamation->identifiant}",
            $reclamation->canal            ? "📂 Canal : {$reclamation->canalLabel()}"            : null,
            $reclamation->type_reclamation ? "📌 Type : {$reclamation->type_reclamation}"         : null,
            $reclamation->priorite === 'haute' ? "⚡ Priorité : Haute"                             : null,
            $reclamation->infos            ? "📋 Infos :\n{$reclamation->infos}"                  : null,
        ]));

        $chatwootId = $this->createChatwootConversationFor(
            reference:      $reclamation->reference,
            sender:         $sender,
            identifiant:    $reclamation->identifiant,
            initialMessage: $initialMessage,
            logContext:     'réclamation',
        );

        if ($chatwootId) {
            $reclamation->update(['chatwoot_conversation_id' => $chatwootId]);
        }

        Log::info('[KASH] Réclamation créée', [
            'reference'               => $reclamation->reference,
            'identifiant'             => $reclamation->identifiant,
            'priorite'                => $reclamation->priorite,
            'chatwoot_conversation_id' => $chatwootId,
        ]);

        return $reclamation;
    }

    // ── Escalade ─────────────────────────────────────────────────────────────

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

        $initialMessage = implode("\n", array_filter([
            "🤖 *Transfert depuis Kash Bot* — {$escalade->reference}",
            "👤 Identifiant : {$escalade->identifiant}",
            $escalade->raison ? "⚠️ Raison : {$escalade->raison}"   : null,
            $escalade->resume ? "📋 Résumé :\n{$escalade->resume}"  : null,
        ]));

        $chatwootId = $this->createChatwootConversationFor(
            reference:      $escalade->reference,
            sender:         $sender,
            identifiant:    $escalade->identifiant,
            initialMessage: $initialMessage,
            logContext:     'escalade',
        );

        if ($chatwootId) {
            $escalade->update(['chatwoot_conversation_id' => $chatwootId]);
        }

        Log::info('[KASH] Escalade créée', [
            'reference'               => $escalade->reference,
            'chatwoot_conversation_id' => $chatwootId,
        ]);

        return $escalade;
    }

    // ── Vérification ticket actif (source de vérité = Chatwoot) ─────────────

    /**
     * Retourne true si le sender a un ticket ouvert (en_attente|en_cours).
     * Si le ticket a un chatwoot_conversation_id, on interroge l'API Chatwoot
     * en temps réel pour avoir le vrai statut — pas le cache local.
     * Le statut local est mis à jour en cas de désynchronisation.
     */
    public function hasActiveTicket(string $sender): bool
    {
        $models = [BotEscalade::class, BotReclamation::class];

        foreach ($models as $modelClass) {
            $ticket = $modelClass::where('sender', $sender)
                ->whereIn('statut', ['en_attente', 'en_cours'])
                ->latest()
                ->first();

            if (!$ticket) {
                continue;
            }

            // Pas de conversation Chatwoot liée → on fait confiance au statut local
            if (!$ticket->chatwoot_conversation_id) {
                return true;
            }

            // Interroger Chatwoot pour le statut réel
            try {
                $conversation = $this->chatwoot->getConversation($ticket->chatwoot_conversation_id);
                $chatwootStatus = $conversation['status'] ?? null;

                if ($chatwootStatus === 'resolved') {
                    // Chatwoot dit résolu → on met à jour le local et on laisse le bot répondre
                    $ticket->update(['statut' => 'resolue']);

                    Log::info('[KASH] Ticket resync depuis Chatwoot : résolu', [
                        'reference'               => $ticket->reference,
                        'chatwoot_conversation_id' => $ticket->chatwoot_conversation_id,
                    ]);

                    continue; // pas actif
                }

                // open|pending|snoozed → ticket toujours ouvert
                return true;

            } catch (\Exception $e) {
                Log::warning('[KASH] Impossible de vérifier statut Chatwoot, fallback local', [
                    'reference' => $ticket->reference,
                    'error'     => $e->getMessage(),
                ]);

                // En cas d'erreur API, on conserve le statut local
                return true;
            }
        }

        return false;
    }

    // ── Mise à jour statut via Chatwoot webhook ───────────────────────────────

    public function syncEscaladeFromChatwoot(int $chatwootConversationId, string $chatwootStatus): void
    {
        $newStatut = match ($chatwootStatus) {
            'resolved' => 'resolue',
            'open'     => 'en_cours',
            'pending'  => 'en_attente',
            default    => null,
        };

        if (!$newStatut) {
            return;
        }

        $this->syncModelFromChatwoot(BotEscalade::class, $chatwootConversationId, $newStatut, 'Escalade');
        $this->syncModelFromChatwoot(BotReclamation::class, $chatwootConversationId, $newStatut, 'Réclamation');
    }

    private function syncModelFromChatwoot(string $modelClass, int $chatwootConversationId, string $newStatut, string $label): void
    {
        $record = $modelClass::where('chatwoot_conversation_id', $chatwootConversationId)->first();

        if (!$record || $record->statut === $newStatut) {
            return;
        }

        $record->update(['statut' => $newStatut]);

        Log::info("[KASH] {$label} synchronisée depuis Chatwoot", [
            'reference'               => $record->reference,
            'chatwoot_conversation_id' => $chatwootConversationId,
            'nouveau_statut'          => $newStatut,
        ]);
    }

    // ── Chatwoot conversation générique ──────────────────────────────────────

    private function createChatwootConversationFor(
        string $reference,
        string $sender,
        string $identifiant,
        string $initialMessage,
        string $logContext,
    ): ?int {
        try {
            $phone = str_replace('whatsapp:', '', $sender);

            $contact   = $this->findOrCreateContact($phone, $identifiant);
            $contactId = $contact['id'];
            $sourceId  = $this->getSourceId($contact, $sender);

            Contact::firstOrCreate(
                ['phone_number' => $phone],
                ['name' => $identifiant, 'chatwoot_contact_id' => $contactId]
            );

            $conversation = $this->chatwoot->createConversation(
                sourceId:       $sourceId,
                inboxId:        (int) config('chatwoot.whatsapp_inbox_id'),
                contactId:      $contactId,
                initialMessage: $initialMessage,
            );

            return $conversation['id'] ?? $conversation['conversation_id'] ?? null;

        } catch (\Exception $e) {
            Log::error("[KASH] Erreur création conversation Chatwoot pour {$logContext}", [
                'reference' => $reference,
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
