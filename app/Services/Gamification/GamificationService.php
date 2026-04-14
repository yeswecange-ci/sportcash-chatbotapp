<?php

namespace App\Services\Gamification;

use App\Models\Client;
use App\Models\Game;
use App\Models\GameAnswer;
use App\Models\GameParticipation;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Vérifie l'éligibilité d'un participant et retourne les infos nécessaires au flow.
     */
    public function checkParticipant(string $slug, string $phone): array
    {
        $game = Game::where('slug', $slug)->first();

        if (!$game) {
            return [
                'eligible'            => 'false',
                'already_participated' => 'false',
                'name_known'          => 'false',
                'name'                => '',
                'game_name'           => '',
                'ended'               => 'true',
            ];
        }

        // Jeu fermé ou expiré
        $ended = $game->status === 'closed' || $game->isEnded();

        // Éligibilité par type de client
        $eligible = true;
        $client = Client::where('phone_number', $phone)->first();

        if ($game->eligibility === 'clients_only') {
            $eligible = $client && $client->is_client;
        }

        // Vérification participation déjà existante
        $alreadyParticipated = GameParticipation::where('game_id', $game->id)
            ->where('phone_number', $phone)
            ->exists();

        // Recherche du nom dans la table clients
        $nameKnown = false;
        $name = '';

        if ($client) {
            $name = $client->client_full_name ?? $client->whatsapp_profile_name ?? '';
            $nameKnown = !empty($name);
        }

        // Limite participants
        if ($game->max_participants !== null) {
            $count = GameParticipation::where('game_id', $game->id)->count();
            if ($count >= $game->max_participants) {
                $eligible = false;
            }
        }

        return [
            'eligible'            => ($eligible && !$ended && !$alreadyParticipated) ? 'true' : 'false',
            'already_participated' => $alreadyParticipated ? 'true' : 'false',
            'name_known'          => $nameKnown ? 'true' : 'false',
            'name'                => $name,
            'game_name'           => $game->name,
            'ended'               => $ended ? 'true' : 'false',
        ];
    }

    /**
     * Crée une participation pour un participant.
     * Utilise une transaction avec verrou pour éviter la race condition sur max_participants.
     */
    public function startParticipation(int $gameId, string $phone, ?string $name): ?GameParticipation
    {
        return DB::transaction(function () use ($gameId, $phone, $name) {
            // Verrou exclusif sur la ligne Game pour sérialiser les inscriptions
            $game = Game::lockForUpdate()->find($gameId);

            if (!$game) {
                return null;
            }

            // Re-vérification atomique sous verrou : évite que 2 requêtes simultanées
            // passent toutes les deux alors qu'il ne reste qu'une place
            if ($game->max_participants !== null) {
                $currentCount = GameParticipation::where('game_id', $gameId)->count();
                if ($currentCount >= $game->max_participants) {
                    return null;
                }
            }

            return GameParticipation::firstOrCreate(
                ['game_id' => $gameId, 'phone_number' => $phone],
                [
                    'participant_name' => $name,
                    'status'           => 'started',
                    'started_at'       => now(),
                ]
            );
        });
    }

    /**
     * Enregistre une réponse à une question (par son ordre).
     */
    public function saveAnswer(int $gameId, string $phone, int $questionOrder, string $answer): bool
    {
        $participation = GameParticipation::where('game_id', $gameId)
            ->where('phone_number', $phone)
            ->first();

        if (!$participation) {
            return false;
        }

        $question = \App\Models\GameQuestion::where('game_id', $gameId)
            ->where('order', $questionOrder)
            ->first();

        if (!$question) {
            return false;
        }

        $isCorrect = $question->checkAnswer($answer);

        GameAnswer::updateOrCreate(
            ['participation_id' => $participation->id, 'question_id' => $question->id],
            ['answer_text' => $answer, 'answered_at' => now(), 'is_correct' => $isCorrect]
        );

        return true;
    }

    /**
     * Enregistre le nom du participant (demandé dans le flow si inconnu).
     */
    public function saveName(int $gameId, string $phone, string $name): void
    {
        GameParticipation::where('game_id', $gameId)
            ->where('phone_number', $phone)
            ->update(['participant_name' => $name]);
    }

    /**
     * Marque la participation comme complète.
     */
    public function completeParticipation(int $gameId, string $phone): void
    {
        $participation = GameParticipation::where('game_id', $gameId)
            ->where('phone_number', $phone)
            ->first();

        $participation?->complete();
    }
}
