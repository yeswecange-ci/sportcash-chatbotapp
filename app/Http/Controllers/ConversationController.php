<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignConversationRequest;
use App\Http\Requests\SendMessageRequest;
use App\Services\Chatwoot\ConversationService;
use App\Services\Chatwoot\MessageService;
use App\Services\Chatwoot\ChatwootClient;
use App\Services\Twilio\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConversationController extends Controller
{
    public function __construct(
        private ConversationService $conversations,
        private MessageService $messages,
        private ChatwootClient $chatwoot,
        private TwilioService $twilio,
    ) {}

    /**
     * Liste des conversations
     * GET /conversations
     */
    public function index(Request $request)
    {
        $status       = $request->get('status', 'open');
        $assigneeType = $request->get('assignee_type', 'all');
        $page         = max(1, min((int) $request->get('page', 1), 100));
        $search       = $request->get('q');
        $label        = $request->get('label');

        if ($search) {
            $data = $this->conversations->search($search, $page);
        } elseif ($label) {
            $data = $this->conversations->filterByLabel($label, $status, $page);
        } else {
            $data = $this->conversations->list($status, $assigneeType, $page);
        }

        // Récupérer les agents pour le filtre d'assignation
        $agents = $this->chatwoot->listAgents();

        return view('conversations.index', [
            'conversations'          => $data['conversations'],
            'meta'                   => $data['meta'],
            'agents'                 => $agents,
            'currentStatus'          => $status,
            'currentAssignee'        => $assigneeType,
            'currentSearch'          => $search,
            'currentLabel'           => $label,
            'currentPage'            => $page,
            'selectedConversationId' => null,
        ]);
    }

    /**
     * Fil de discussion d'une conversation
     * GET /conversations/{id}
     * Rend la meme vue index avec auto-ouverture de la conversation
     */
    public function show(int $conversationId, Request $request)
    {
        $status       = $request->get('status', 'open');
        $assigneeType = $request->get('assignee_type', 'all');
        $page         = max(1, min((int) $request->get('page', 1), 100));
        $search       = $request->get('q');
        $label        = $request->get('label');

        if ($search) {
            $data = $this->conversations->search($search, $page);
        } elseif ($label) {
            $data = $this->conversations->filterByLabel($label, $status, $page);
        } else {
            $data = $this->conversations->list($status, $assigneeType, $page);
        }

        $agents = $this->chatwoot->listAgents();

        return view('conversations.index', [
            'conversations'          => $data['conversations'],
            'meta'                   => $data['meta'],
            'agents'                 => $agents,
            'currentStatus'          => $status,
            'currentAssignee'        => $assigneeType,
            'currentSearch'          => $search,
            'currentLabel'           => $label,
            'currentPage'            => $page,
            'selectedConversationId' => $conversationId,
        ]);
    }

    /**
     * AJAX — Panneau chat (HTML partial)
     * GET /ajax/conversations/{id}/panel
     */
    public function panel(int $conversationId)
    {
        $data   = $this->conversations->getWithMessages($conversationId);
        $agents = $this->chatwoot->listAgents();

        // Détection fenêtre WhatsApp 24h expirée (uniquement pour les channels WhatsApp/TwilioSms)
        $twilioConfigured = $this->twilio->isConfigured();
        $windowExpired = false;
        $channel = $data['conversation']->channel;
        if ($twilioConfigured && in_array($channel, ['Channel::TwilioSms', 'Channel::Whatsapp', 'twilio_sms', 'whatsapp'])) {
            $windowExpired = $this->conversations->isWindowExpired($data['messages']);
        }

        return view('conversations._panel', [
            'conversation'    => $data['conversation'],
            'messages'        => $data['messages'],
            'contact'         => $data['contact'],
            'assignee'        => $data['assignee'],
            'agents'          => $agents,
            'windowExpired'   => $windowExpired,
            'twilioConfigured' => $twilioConfigured,
        ]);
    }

    /**
     * AJAX — Polling nouveaux messages
     * GET /ajax/conversations/{id}/poll?last_message_id=X
     */
    public function poll(int $conversationId, Request $request): JsonResponse
    {
        $lastId = (int) $request->get('last_message_id', 0);

        $newMessages = $this->conversations->getNewMessages($conversationId, $lastId);

        return response()->json([
            'messages' => $newMessages,
            'count'    => count($newMessages),
        ]);
    }

    /**
     * AJAX — Envoyer un message
     * POST /ajax/conversations/{id}/messages
     */
    public function sendMessage(int $conversationId, SendMessageRequest $request): JsonResponse
    {
        $content     = $request->input('content', '');
        $attachments = $request->file('attachments', []);

        $result = $request->boolean('is_private')
            ? $this->messages->sendPrivateNote($conversationId, $content, $attachments)
            : $this->messages->sendToCustomer($conversationId, $content, $attachments);

        return response()->json($result);
    }

    /**
     * AJAX — Changer statut
     * POST /ajax/conversations/{id}/status
     */
    public function toggleStatus(int $conversationId, Request $request): JsonResponse
    {
        $request->validate(['action' => 'required|in:resolve,reopen,pending']);

        $result = match ($request->action) {
            'resolve' => $this->conversations->resolve($conversationId),
            'reopen'  => $this->conversations->reopen($conversationId),
            'pending' => $this->chatwoot->toggleStatus($conversationId, 'pending'),
        };

        return response()->json($result);
    }

    /**
     * AJAX — Assigner
     * POST /ajax/conversations/{id}/assign
     */
    public function assign(int $conversationId, AssignConversationRequest $request): JsonResponse
    {
        $result = $this->conversations->assign($conversationId, $request->agent_id);

        return response()->json($result);
    }

    /**
     * AJAX — Marquer conversation comme lue
     * POST /ajax/conversations/{id}/read
     */
    public function markRead(int $conversationId): JsonResponse
    {
        try {
            $this->chatwoot->markConversationRead($conversationId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * AJAX — Compteurs sidebar
     * GET /ajax/counts
     */
    public function counts(): JsonResponse
    {
        return response()->json($this->conversations->getCounts());
    }

    /**
     * AJAX — Typing indicator
     * POST /ajax/conversations/{id}/typing
     */
    public function typing(int $conversationId, Request $request): JsonResponse
    {
        $status = $request->get('typing_status', 'on');
        $this->chatwoot->toggleTyping($conversationId, $status);

        return response()->json(['success' => true]);
    }

    /**
     * AJAX — Messages anciens (pagination scroll up)
     * GET /ajax/conversations/{id}/messages-before
     */
    public function messagesBefore(int $conversationId, Request $request): JsonResponse
    {
        $beforeId = (int) $request->get('before', 0);

        if (!$beforeId) {
            return response()->json(['messages' => []]);
        }

        try {
            $data = $this->chatwoot->getMessagesBefore($conversationId, $beforeId);
            return response()->json([
                'messages' => $data['payload'] ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json(['messages' => []]);
        }
    }

    /**
     * AJAX — Supprimer un message
     * DELETE /ajax/conversations/{id}/messages/{messageId}
     */
    public function deleteMessage(int $conversationId, int $messageId): JsonResponse
    {
        try {
            $this->chatwoot->deleteMessage($conversationId, $messageId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Supprimer une conversation
     * DELETE /ajax/conversations/{id}
     */
    public function destroy(int $conversationId): JsonResponse
    {
        try {
            $this->chatwoot->deleteConversation($conversationId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Filtres avances
     * POST /ajax/conversations/filter
     */
    public function filter(Request $request): JsonResponse
    {
        $filters = $request->input('filters', []);
        $page = (int) $request->input('page', 1);

        try {
            $data = $this->conversations->advancedFilter($filters, $page);

            return response()->json([
                'conversations' => collect($data['conversations'])->map(fn($c) => [
                    'id'               => $c->id,
                    'status'           => $c->status,
                    'statusLabel'      => $c->statusLabel(),
                    'contactName'      => $c->contactName,
                    'contactPhone'     => $c->contactPhone,
                    'contactThumbnail' => $c->contactThumbnail,
                    'lastMessage'      => $c->lastMessage,
                    'timeAgo'          => $c->timeAgo(),
                    'unreadCount'      => $c->unreadCount,
                    'assigneeName'     => $c->assigneeName,
                    'labels'           => $c->labels,
                    'statusBadgeClass' => $c->statusBadgeClass(),
                ])->all(),
                'meta' => $data['meta'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['conversations' => [], 'meta' => []], 500);
        }
    }

    /**
     * AJAX — Liste des templates Twilio WhatsApp
     * GET /ajax/twilio/templates
     */
    public function twilioTemplates(): JsonResponse
    {
        if (!$this->twilio->isConfigured()) {
            return response()->json(['error' => 'Twilio non configuré'], 500);
        }

        try {
            $templates = $this->twilio->getContentTemplates();
            return response()->json($templates);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Envoyer un template WhatsApp via Twilio
     * POST /ajax/conversations/{conversationId}/template-message
     */
    public function sendTemplateMessage(int $conversationId, Request $request): JsonResponse
    {
        $request->validate([
            'content_sid' => 'required|string|starts_with:HX',
            'variables'   => 'nullable|array',
            'template_name' => 'nullable|string',
            'body_preview'  => 'nullable|string',
        ]);

        if (!$this->twilio->isConfigured()) {
            return response()->json(['error' => 'Twilio non configuré'], 500);
        }

        try {
            $conversation = $this->chatwoot->getConversation($conversationId);
            $phone = $conversation['meta']['sender']['phone_number'] ?? null;

            if (!$phone) {
                return response()->json(['error' => 'Numéro de téléphone du contact introuvable'], 422);
            }

            // Envoyer via Twilio
            $variables = $request->input('variables', []);
            $this->twilio->sendTemplate($phone, $request->content_sid, $variables);

            // Logger dans Chatwoot comme NOTE PRIVÉE pour éviter le double envoi via webhook
            $templateName = $request->input('template_name', 'Template');
            $bodyPreview = $request->input('body_preview', '');
            $logContent = "[Template: {$templateName}]\n{$bodyPreview}";

            $chatwootMsg = $this->chatwoot->sendMessage($conversationId, $logContent, true);

            return response()->json($chatwootMsg);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Liste legere pour polling sidebar (unread + last message)
     * GET /ajax/conversations/list-update
     */
    public function listUpdate(Request $request): JsonResponse
    {
        $status       = $request->get('status', 'open');
        $assigneeType = $request->get('assignee_type', 'all');

        try {
            $inboxId  = (int) config('chatwoot.whatsapp_inbox_id') ?: null;
            $response = $this->chatwoot->listConversations($status, $assigneeType, 1, $inboxId);
            $conversations = collect($response['data']['payload'] ?? [])->map(function ($conv) {
                $lastMsg = $conv['last_non_activity_message'] ?? $conv['messages'][0] ?? [];
                $lastMsgType = $lastMsg['message_type'] ?? null; // 0=incoming(client), 1=outgoing(agent), 2=activity

                $sender = $conv['meta']['sender'] ?? [];

                return [
                    'id'                => $conv['id'],
                    'unread_count'      => $conv['unread_count'] ?? 0,
                    'last_message'      => $lastMsg['content'] ?? null,
                    'last_message_type' => $lastMsgType,
                    'status'            => $conv['status'] ?? 'open',
                    'contact_name'      => $sender['name'] ?? 'Inconnu',
                    'contact_thumbnail' => $sender['thumbnail'] ?? null,
                ];
            })->all();

            return response()->json(['conversations' => $conversations]);
        } catch (\Exception $e) {
            return response()->json(['conversations' => []]);
        }
    }
}
