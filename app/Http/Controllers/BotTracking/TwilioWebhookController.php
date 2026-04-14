<?php

namespace App\Http\Controllers\BotTracking;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationEvent;
use App\Models\DailyStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\TwiML\MessagingResponse;

class TwilioWebhookController extends Controller
{
    /**
     * Handle incoming WhatsApp messages from Twilio
     */
    public function handleIncomingMessage(Request $request)
    {
        try {
            // Validate incoming Twilio data
            $validated = $request->validate([
                'From' => 'required|string',
                'Body' => 'nullable|string|max:1600', // WhatsApp message limit
                'MessageSid' => 'required|string',
                'ProfileName' => 'nullable|string|max:255',
                'NumMedia' => 'nullable|integer|min:0',
                'MediaUrl0' => 'nullable|url',
                'MediaContentType0' => 'nullable|string',
            ]);

            // Log incoming request for debugging
            Log::info('Twilio Incoming Message', $validated);

            // Extract Twilio message data
            $from = $validated['From']; // Format: whatsapp:+212XXXXXXXXX
            $body = $validated['Body'] ?? '';
            $messageId = $validated['MessageSid'];
            $profileName = $validated['ProfileName'] ?? null;
            $numMedia = $validated['NumMedia'] ?? 0;

            // Clean phone number (remove whatsapp: prefix)
            $phoneNumber = str_replace('whatsapp:', '', $from);

            // Synchronize with Client table FIRST to get existing data
            $client = \App\Models\Client::findOrCreateByPhone($phoneNumber);

            // Check if client already exists (has interaction history)
            $clientExists = $client->wasRecentlyCreated === false && $client->client_full_name !== null;

            // Transaction + verrou pour éviter les doublons de conversations (race condition Twilio retries)
            $conversation = DB::transaction(function () use ($phoneNumber, $profileName, $client) {
                $conversation = Conversation::where('phone_number', $phoneNumber)
                    ->where('status', 'active')
                    ->where('last_activity_at', '>', now()->subHours(24))
                    ->latest()
                    ->lockForUpdate()
                    ->first();

                if (!$conversation) {
                    $conversation = Conversation::create([
                        'phone_number'          => $phoneNumber,
                        'session_id'            => uniqid('session_', true),
                        'whatsapp_profile_name' => $profileName ?? 'Client WhatsApp',
                        'client_full_name'      => $client->client_full_name,
                        'is_client'             => $client->is_client,
                        'email'                 => $client->email,
                        'vin'                   => $client->vin,
                        'carte_vip'             => $client->carte_vip,
                        'started_at'            => now(),
                        'last_activity_at'      => now(),
                        'current_menu'          => 'main_menu',
                        'status'                => 'active',
                    ]);

                    Log::info('New conversation created', [
                        'phone'            => $phoneNumber,
                        'client_full_name' => $client->client_full_name,
                        'conversation_id'  => $conversation->id,
                    ]);

                    DailyStatistic::today()->increment('total_conversations');
                } else {
                    $updates = ['last_activity_at' => now()];

                    if ($profileName && $conversation->whatsapp_profile_name !== $profileName) {
                        $updates['whatsapp_profile_name'] = $profileName;
                    }

                    if (!$conversation->client_full_name && $client->client_full_name) {
                        $updates['client_full_name'] = $client->client_full_name;
                    }

                    $conversation->update($updates);
                }

                return $conversation;
            });

            // Update client information - update WhatsApp profile name (always)
            if ($profileName) {
                $client->update(['whatsapp_profile_name' => $profileName]);
            }

            // Idempotence : éviter le double traitement si Twilio retry le même message
            // On vérifie si le MessageSid a déjà été enregistré pour cette conversation
            $alreadyProcessed = ConversationEvent::where('conversation_id', $conversation->id)
                ->where('event_type', 'message_received')
                ->whereJsonContains('metadata->message_sid', $messageId)
                ->exists();

            if (!$alreadyProcessed) {
                $client->incrementInteractions();

                // Prepare metadata for the event
                $metadata = [
                    'message_sid' => $messageId,
                    'profile_name' => $profileName,
                ];

                // Handle media attachments (images, videos, audio)
                if ($numMedia > 0) {
                    $mediaItems = [];

                    for ($i = 0; $i < min($numMedia, 10); $i++) {
                        $mediaUrl = $request->input("MediaUrl{$i}");
                        $mediaType = $request->input("MediaContentType{$i}");

                        if ($mediaUrl) {
                            $mediaItems[] = [
                                'url' => $mediaUrl,
                                'type' => $mediaType,
                            ];
                        }
                    }

                    $metadata['media'] = $mediaItems;
                    $metadata['media_count'] = $numMedia;
                }

                // Store the incoming message as an event
                ConversationEvent::create([
                    'conversation_id' => $conversation->id,
                    'event_type' => 'message_received',
                    'user_input' => $body,
                    'metadata' => $metadata,
                ]);
            }

            // Return conversation data to Twilio Flow
            // IMPORTANT: Twilio Flow compare avec des chaînes "true"/"false", pas des booléens
            $responseData = [
                'success' => true,
                'conversation_id' => $conversation->id,
                'session_id' => $conversation->session_id,
                'phone_number' => $phoneNumber,
                'current_menu' => $conversation->current_menu,
                'is_client' => $client->is_client ?? $conversation->is_client,
                'client_full_name' => $client->client_full_name ?? $conversation->client_full_name,
                'whatsapp_profile_name' => $client->whatsapp_profile_name ?? $conversation->whatsapp_profile_name,
                'profile_name' => $profileName ?? $conversation->whatsapp_profile_name,
                'message' => $body,
                'status' => $conversation->status,
                'has_media' => $numMedia > 0 ? 'true' : 'false',
                'media_count' => $numMedia,
                'client_exists' => $clientExists ? 'true' : 'false',
                'client_has_name' => $client->client_full_name !== null ? 'true' : 'false',
                'client_status_known' => $client->is_client !== null ? 'true' : 'false',
            ];

            // Log pour debugging
            Log::info('API Response to Twilio', [
                'phone' => $phoneNumber,
                'client_has_name' => $responseData['client_has_name'],
                'client_status_known' => $responseData['client_status_known'],
                'client_full_name' => $responseData['client_full_name'],
            ]);

            return response()->json($responseData);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Twilio Webhook Validation Error', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invalid request data',
                'details' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Twilio Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle menu choice from Twilio Flow
     */
    public function handleMenuChoice(Request $request)
    {
        try {
            Log::info('Twilio Menu Choice', $request->all());

            $conversationId = $request->input('conversation_id');
            $menuChoice     = $request->input('menu_choice');   // nom du widget/menu
            $userInput      = $request->input('user_input');    // valeur saisie (ex: "1")
            $choiceLabel    = $request->input('choice_label', $userInput); // label lisible (ex: "Informations")

            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                return response()->json(['success' => false, 'error' => 'Conversation not found'], 404);
            }

            // Update conversation menu + menu_path dans une transaction avec verrou
            // pour éviter la race condition si Twilio envoie deux requêtes simultanées
            $menuPath = DB::transaction(function () use ($conversation, $menuChoice) {
                $conversation = Conversation::lockForUpdate()->find($conversation->id);

                $rawPath = $conversation->menu_path;
                $menuPath = is_array($rawPath) ? $rawPath : (is_string($rawPath) ? (json_decode($rawPath, true) ?? []) : []);
                $menuPath[] = $menuChoice;

                $conversation->update([
                    'current_menu'    => $menuChoice,
                    'last_activity_at' => now(),
                    'menu_path'       => $menuPath,
                ]);

                return $menuPath;
            });

            // Store event avec menu_name + choice_label pour stats dynamiques
            ConversationEvent::create([
                'conversation_id' => $conversation->id,
                'event_type'      => 'menu_choice',
                'user_input'      => $userInput,
                'menu_name'       => $menuChoice,
                'choice_label'    => $choiceLabel,
                'widget_name'     => $menuChoice,
                'metadata'        => ['menu_choice' => $menuChoice, 'choice_label' => $choiceLabel],
            ]);

            // Stats dynamiques — fonctionne peu importe le flow Twilio
            if ($menuChoice && $userInput !== null) {
                DailyStatistic::today()->incrementMenuStats($menuChoice, $choiceLabel ?: $userInput);
            }

            return response()->json([
                'success' => true,
                'current_menu' => $menuChoice,
                'menu_path' => $menuPath,
            ]);

        } catch (\Exception $e) {
            Log::error('Menu Choice Error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle free text input from user
     */
    public function handleFreeInput(Request $request)
    {
        try {
            Log::info('Twilio Free Input', $request->all());

            $conversationId = $request->input('conversation_id');
            $userInput = $request->input('user_input');
            $widgetName = $request->input('widget_name');

            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                return response()->json(['success' => false, 'error' => 'Conversation not found'], 404);
            }

            // Update last activity
            $conversation->update(['last_activity_at' => now()]);

            // Store free input event
            ConversationEvent::create([
                'conversation_id' => $conversation->id,
                'event_type' => 'free_input',
                'user_input' => $userInput,
                'widget_name' => $widgetName,
            ]);

            // Update conversation data based on widget
            $this->updateConversationData($conversation, $widgetName, $userInput);

            return response()->json([
                'success' => true,
                'stored' => true,
            ]);

        } catch (\Exception $e) {
            Log::error('Free Input Error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Complete a conversation
     */
    public function completeConversation(Request $request)
    {
        try {
            Log::info('Twilio Complete Conversation', $request->all());

            $conversationId = $request->input('conversation_id');
            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                return response()->json(['success' => false, 'error' => 'Conversation not found'], 404);
            }

            // Calculate duration
            $durationSeconds = $conversation->started_at ?
                $conversation->started_at->diffInSeconds(now()) : 0;

            // Update conversation
            $conversation->update([
                'status' => 'completed',
                'ended_at' => now(),
                'duration_seconds' => $durationSeconds,
                'last_activity_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'completed' => true,
                'duration_seconds' => $durationSeconds,
            ]);

        } catch (\Exception $e) {
            Log::error('Complete Conversation Error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update conversation data based on widget input
     */
    private function updateConversationData($conversation, $widgetName, $userInput)
    {
        switch ($widgetName) {
            case 'collect_name':
                // Synchroniser avec la table clients
                $client = \App\Models\Client::findOrCreateByPhone($conversation->phone_number);

                // ⚠️ IMPORTANT : Ne PAS écraser si le client a déjà un nom complet
                if (!$client->client_full_name) {
                    $client->update(['client_full_name' => $userInput]);
                    // Stocker aussi dans la conversation
                    $conversation->update(['client_full_name' => $userInput]);

                    Log::info('Client full name saved', [
                        'phone' => $conversation->phone_number,
                        'name' => $userInput
                    ]);
                } else {
                    Log::warning('Attempted to overwrite existing client_full_name', [
                        'phone' => $conversation->phone_number,
                        'existing_name' => $client->client_full_name,
                        'attempted_name' => $userInput
                    ]);

                    // Copier le nom existant dans la conversation
                    $conversation->update(['client_full_name' => $client->client_full_name]);
                }
                break;

            case 'collect_email':
                $conversation->update(['email' => $userInput]);

                // Synchroniser avec la table clients
                $client = \App\Models\Client::findOrCreateByPhone($conversation->phone_number);
                if (!$client->email) {
                    $client->update(['email' => $userInput]);
                }
                break;

            case 'collect_vin':
                $conversation->update(['vin' => $userInput]);

                // Synchroniser avec la table clients
                $client = \App\Models\Client::findOrCreateByPhone($conversation->phone_number);
                if (!$client->vin) {
                    $client->update(['vin' => $userInput]);
                }
                break;

            case 'collect_carte_vip':
                $conversation->update(['carte_vip' => $userInput]);

                // Synchroniser avec la table clients
                $client = \App\Models\Client::findOrCreateByPhone($conversation->phone_number);
                if (!$client->carte_vip) {
                    $client->update(['carte_vip' => $userInput]);
                }
                break;

            case 'check_client':
                $isClient = in_array($userInput, ['1', 'oui', 'yes']);
                $conversation->update(['is_client' => $isClient]);

                // Synchroniser avec la table clients
                $client = \App\Models\Client::findOrCreateByPhone($conversation->phone_number);
                if ($client->is_client === null) {
                    $client->update(['is_client' => $isClient]);
                }
                break;
        }
    }
}
