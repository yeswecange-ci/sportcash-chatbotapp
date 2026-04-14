<?php

namespace App\Http\Controllers\BotTracking;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationEvent;
use App\Models\DailyStatistic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    /**
     * Recevoir un événement depuis n8n/Twilio
     * 
     * POST /api/webhook/event
     */
    public function handleEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id'          => 'required|string',
            'phone_number'        => 'required|string',
            'event_type'          => 'required|string',
            'widget_name'         => 'nullable|string',
            'widget_type'         => 'nullable|string',
            'user_input'          => 'nullable|string',
            'expected_input_type' => 'nullable|string',
            'bot_message'         => 'nullable|string',
            'media_url'           => 'nullable|url',
            'menu_name'           => 'nullable|string',
            'choice_label'        => 'nullable|string',
            'menu_path'           => 'nullable|array',
            'metadata'            => 'nullable|array',
            'response_time_ms'    => 'nullable|integer|min:0',
            'timestamp'           => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Trouver ou créer la conversation
            $conversation = Conversation::findOrCreateBySession(
                $request->session_id,
                $request->phone_number
            );

            // Mettre à jour l'activité
            $conversation->last_activity_at = now();
            
            // Mettre à jour le menu actuel si fourni
            if ($request->menu_name) {
                $conversation->current_menu = $request->menu_name;
            }
            
            // Mettre à jour le widget actuel
            if ($request->widget_name) {
                $conversation->last_widget = $request->widget_name;
            }

            // Mettre à jour le chemin si fourni
            if ($request->menu_path) {
                $conversation->menu_path = $request->menu_path;
            }

            $conversation->save();

            // Créer l'événement
            $event = ConversationEvent::create([
                'conversation_id' => $conversation->id,
                'event_type' => $request->event_type,
                'widget_name' => $request->widget_name,
                'widget_type' => $request->widget_type,
                'user_input' => $request->user_input,
                'expected_input_type' => $request->expected_input_type,
                'bot_message' => $request->bot_message,
                'media_url' => $request->media_url,
                'menu_name' => $request->menu_name,
                'choice_label' => $request->choice_label,
                'menu_path' => $request->menu_path,
                'metadata' => $request->metadata,
                'response_time_ms' => $request->response_time_ms,
                'event_at' => $request->timestamp ? new \DateTime($request->timestamp) : now(),
            ]);

            // Mettre à jour les statistiques quotidiennes
            $this->updateDailyStats($request->event_type, $request->user_input, $request->menu_name);

            Log::info('Event logged', [
                'conversation_id' => $conversation->id,
                'event_id' => $event->id,
                'event_type' => $request->event_type
            ]);

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
                'event_id' => $event->id
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour les données utilisateur
     * 
     * POST /api/webhook/user-data
     */
    public function updateUserData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id'       => 'required|string',
            'nom_prenom'       => 'nullable|string',   // legacy — mappé vers client_full_name
            'client_full_name' => 'nullable|string',
            'is_client'        => 'nullable|boolean',
            'email'            => 'nullable|email',
            'vin'              => 'nullable|string|max:17',
            'carte_vip'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $conversation = Conversation::where('session_id', $request->session_id)->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversation not found'
                ], 404);
            }

            // Capturer la valeur précédente avant mise à jour (pour les stats)
            $previousIsClient = $conversation->is_client;

            // Mapping legacy : nom_prenom (n8n) → client_full_name (colonne réelle)
            if ($request->has('nom_prenom') && !$request->has('client_full_name')) {
                $conversation->client_full_name = $request->nom_prenom;
            }

            // Champs directs
            $fieldsToUpdate = ['client_full_name', 'is_client', 'email', 'vin', 'carte_vip'];
            foreach ($fieldsToUpdate as $field) {
                if ($request->has($field)) {
                    $conversation->$field = $request->$field;
                }
            }

            $conversation->save();

            // Incrémenter les stats clients/non-clients uniquement au premier classement
            // (quand is_client passe de null à une valeur) pour éviter le double comptage
            if ($request->has('is_client') && $previousIsClient === null) {
                $stat = DailyStatistic::today();
                if ($request->is_client) {
                    $stat->increment('clients_count');
                } else {
                    $stat->increment('non_clients_count');
                }
            }

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id
            ]);

        } catch (\Exception $e) {
            Log::error('User data update error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer une conversation comme terminée
     * 
     * POST /api/webhook/complete
     */
    public function handleComplete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'status' => 'nullable|in:completed,timeout,abandoned',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $conversation = Conversation::where('session_id', $request->session_id)->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversation not found'
                ], 404);
            }

            $status = $request->status ?? 'completed';
            $conversation->status = $status;
            $conversation->ended_at = now();
            if ($conversation->started_at) {
                $conversation->duration_seconds = $conversation->started_at->diffInSeconds($conversation->ended_at);
            }
            $conversation->save();

            // Logger l'événement
            ConversationEvent::create([
                'conversation_id' => $conversation->id,
                'event_type' => 'flow_complete',
                'metadata' => ['final_status' => $status],
                'event_at' => now(),
            ]);

            // Mettre à jour les stats
            $stat = DailyStatistic::today();
            match ($status) {
                'completed' => $stat->increment('completed_conversations'),
                'timeout' => $stat->increment('timeout_conversations'),
                'abandoned' => $stat->increment('abandoned_conversations'),
                default => null
            };

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id
            ]);

        } catch (\Exception $e) {
            Log::error('Complete error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour les statistiques quotidiennes
     */
    private function updateDailyStats(string $eventType, ?string $userInput, ?string $menuName): void
    {
        $stat = DailyStatistic::today();

        // Nouvelle conversation
        if ($eventType === 'flow_start') {
            $stat->increment('total_conversations');
        }

        // Choix de menu principal
        if ($eventType === 'menu_choice' && $menuName === 'menu_principal') {
            $stat->incrementMainMenu($userInput);
        }

        // Entrée invalide
        if ($eventType === 'invalid_input') {
            $stat->increment('invalid_inputs_count');
        }

        // Erreur
        if ($eventType === 'error') {
            $stat->increment('errors_count');
        }
    }
}
