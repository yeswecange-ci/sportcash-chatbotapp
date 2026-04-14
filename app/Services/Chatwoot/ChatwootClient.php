<?php

namespace App\Services\Chatwoot;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class ChatwootClient
{
    private string $baseUrl;
    private int $accountId;
    private string $token;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('chatwoot.base_url'), '/');
        $this->accountId = (int) config('chatwoot.account_id');

        // Use the logged-in agent's own Chatwoot token if available,
        // otherwise fall back to the global admin token from .env
        $this->token = $this->resolveToken();
    }

    private function resolveToken(): string
    {
        $user = auth()->user();

        if ($user && $user->agentToken && $user->agentToken->chatwoot_access_token) {
            return $user->agentToken->chatwoot_access_token;
        }

        return config('chatwoot.api_token');
    }

    // ═══════════════════════════════════════════════
    // HTTP CLIENT
    // ═══════════════════════════════════════════════

    private function client(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'api_access_token' => $this->token,
                'Content-Type'     => 'application/json',
            ])
            ->timeout(15)
            ->retry(2, 500);

        if (!app()->isProduction()) {
            $request->withoutVerifying();
        }

        return $request;
    }

    private function url(string $path): string
    {
        return "/api/v1/accounts/{$this->accountId}/{$path}";
    }

    private function inboxId(): ?int
    {
        $id = (int) config('chatwoot.whatsapp_inbox_id');
        return $id ?: null;
    }

    // ═══════════════════════════════════════════════
    // CONVERSATIONS
    // ═══════════════════════════════════════════════

    /**
     * Lister les conversations
     *
     * @param string $status       open|resolved|pending|snoozed|all
     * @param string $assigneeType me|unassigned|all|assigned
     * @param int    $page
     * @param int|null $inboxId
     * @return array
     */
    public function listConversations(
        string $status = 'open',
        string $assigneeType = 'all',
        int $page = 1,
        ?int $inboxId = null
    ): array {
        $query = compact('status', 'page');
        $query['assignee_type'] = $assigneeType;

        if ($inboxId) {
            $query['inbox_id'] = $inboxId;
        }

        return $this->client()
            ->get($this->url('conversations'), $query)
            ->throw()
            ->json();
    }

    /**
     * Détails d'une conversation
     */
    public function getConversation(int $conversationId): array
    {
        return $this->client()
            ->get($this->url("conversations/{$conversationId}"))
            ->throw()
            ->json();
    }

    /**
     * Créer une conversation (handoff Twilio)
     */
    public function createConversation(
        string $sourceId,
        int $inboxId,
        int $contactId,
        ?string $initialMessage = null,
        ?int $assigneeId = null
    ): array {
        $payload = [
            'source_id'  => $sourceId,
            'inbox_id'   => $inboxId,
            'contact_id' => $contactId,
            'status'     => 'open',
        ];

        if ($assigneeId) {
            $payload['assignee_id'] = $assigneeId;
        }

        if ($initialMessage) {
            $payload['message'] = ['content' => $initialMessage];
        }

        return $this->client()
            ->post($this->url('conversations'), $payload)
            ->throw()
            ->json();
    }

    /**
     * Changer le statut (open, resolved, pending, snoozed)
     */
    public function toggleStatus(int $conversationId, string $status): array
    {
        return $this->client()
            ->post($this->url("conversations/{$conversationId}/toggle_status"), [
                'status' => $status,
            ])
            ->throw()
            ->json();
    }

    /**
     * Assigner à un agent
     */
    public function assignConversation(int $conversationId, int $agentId): array
    {
        return $this->client()
            ->post($this->url("conversations/{$conversationId}/assignments"), [
                'assignee_id' => $agentId,
            ])
            ->throw()
            ->json();
    }

    /**
     * Compteurs par statut
     */
    public function getConversationCounts(): array
    {
        $inboxFilter = array_filter(['inbox_id' => $this->inboxId()]);

        $openMeta = $this->client()
            ->get($this->url('conversations'), array_merge(['status' => 'open', 'page' => 1], $inboxFilter))
            ->throw()
            ->json('data.meta');

        $pendingMeta = $this->client()
            ->get($this->url('conversations'), array_merge(['status' => 'pending', 'page' => 1], $inboxFilter))
            ->throw()
            ->json('data.meta');

        $resolvedMeta = $this->client()
            ->get($this->url('conversations'), array_merge(['status' => 'resolved', 'page' => 1], $inboxFilter))
            ->throw()
            ->json('data.meta');

        return [
            'mine_count'       => $openMeta['mine_count'] ?? 0,
            'assigned_count'   => $openMeta['assigned_count'] ?? 0,
            'unassigned_count' => $openMeta['unassigned_count'] ?? 0,
            'all_count'        => $openMeta['all_count'] ?? 0,
            'open_count'       => $openMeta['all_count'] ?? 0,
            'pending_count'    => $pendingMeta['all_count'] ?? 0,
            'resolved_count'   => $resolvedMeta['all_count'] ?? 0,
        ];
    }

    /**
     * Rechercher dans les conversations
     */
    public function searchConversations(string $query, int $page = 1): array
    {
        $params = array_filter([
            'q'        => $query,
            'page'     => $page,
            'inbox_id' => $this->inboxId(),
        ]);

        return $this->client()
            ->get($this->url('conversations'), $params)
            ->throw()
            ->json();
    }

    // ═══════════════════════════════════════════════
    // MESSAGES
    // ═══════════════════════════════════════════════

    /**
     * Récupérer les messages d'une conversation
     */
    public function getMessages(int $conversationId): array
    {
        return $this->client()
            ->get($this->url("conversations/{$conversationId}/messages"))
            ->throw()
            ->json();
    }

    /**
     * Marquer une conversation comme lue (reset unread_count)
     */
    public function markConversationRead(int $conversationId): void
    {
        $this->client()
            ->post($this->url("conversations/{$conversationId}/update_last_seen"))
            ->throw();
    }

    /**
     * Envoyer un message texte
     */
    public function sendMessage(
        int $conversationId,
        string $content,
        bool $isPrivate = false,
        string $messageType = 'outgoing'
    ): array {
        return $this->client()
            ->post($this->url("conversations/{$conversationId}/messages"), [
                'content'      => $content,
                'message_type' => $messageType,
                'private'      => $isPrivate,
            ])
            ->throw()
            ->json();
    }

    /**
     * Envoyer un message avec fichiers joints (multipart/form-data)
     *
     * @param \Illuminate\Http\UploadedFile[] $attachments
     */
    public function sendMessageWithAttachments(
        int $conversationId,
        ?string $content,
        array $attachments,
        bool $isPrivate = false,
        string $messageType = 'outgoing'
    ): array {
        $multipart = [
            ['name' => 'content',      'contents' => $content ?? ''],
            ['name' => 'message_type', 'contents' => $messageType],
            ['name' => 'private',      'contents' => $isPrivate ? 'true' : 'false'],
        ];

        foreach ($attachments as $file) {
            $multipart[] = [
                'name'     => 'attachments[]',
                'contents' => fopen($file->getRealPath(), 'rb'),
                'filename' => $file->getClientOriginalName(),
                'headers'  => ['Content-Type' => $file->getMimeType() ?: 'application/octet-stream'],
            ];
        }

        $request = Http::baseUrl($this->baseUrl)
            ->withHeaders(['api_access_token' => $this->token])
            ->timeout(90)
            ->asMultipart();

        if (!app()->isProduction()) {
            $request->withoutVerifying();
        }

        return $request
            ->post($this->url("conversations/{$conversationId}/messages"), $multipart)
            ->throw()
            ->json();
    }

    // ═══════════════════════════════════════════════
    // CONTACTS
    // ═══════════════════════════════════════════════

    /**
     * Rechercher un contact (par nom, email ou téléphone)
     */
    public function searchContacts(string $query): array
    {
        return $this->client()
            ->get($this->url('contacts/search'), ['q' => $query])
            ->throw()
            ->json();
    }

    /**
     * Créer un contact
     */
    public function createContact(string $name, string $phoneNumber, ?string $email = null, ?int $inboxId = null): array
    {
        $payload = [
            'name'         => $name,
            'phone_number' => $phoneNumber,
            'inbox_id'     => $inboxId ?? config('chatwoot.whatsapp_inbox_id'),
        ];

        if ($email) {
            $payload['email'] = $email;
        }

        return $this->client()
            ->post($this->url('contacts'), $payload)
            ->throw()
            ->json();
    }

    /**
     * Détails d'un contact
     */
    public function getContact(int $contactId): array
    {
        return $this->client()
            ->get($this->url("contacts/{$contactId}"))
            ->throw()
            ->json();
    }

    /**
     * Inboxes contactables pour un contact donné
     */
    public function getContactableInboxes(int $contactId): array
    {
        return $this->client()
            ->get($this->url("contacts/{$contactId}/contactable_inboxes"))
            ->throw()
            ->json();
    }

    // ═══════════════════════════════════════════════
    // AGENTS
    // ═══════════════════════════════════════════════

    public function listAgents(): array
    {
        return $this->client()
            ->get($this->url('agents'))
            ->throw()
            ->json();
    }

    public function createAgent(string $name, string $email, string $role = 'agent'): array
    {
        return $this->client()
            ->post($this->url('agents'), [
                'name'  => $name,
                'email' => $email,
                'role'  => $role,
            ])
            ->throw()
            ->json();
    }

    public function updateAgent(int $agentId, array $data): array
    {
        return $this->client()
            ->put($this->url("agents/{$agentId}"), $data)
            ->throw()
            ->json();
    }

    public function deleteAgent(int $agentId): void
    {
        $this->client()
            ->delete($this->url("agents/{$agentId}"))
            ->throw();
    }

    // ═══════════════════════════════════════════════
    // REPORTS
    // ═══════════════════════════════════════════════

    /**
     * Rapport par agent
     *
     * @param string $metric conversations_count|avg_first_response_time|avg_resolution_time
     * @param string $since  Timestamp UNIX
     * @param string $until  Timestamp UNIX
     */
    public function getAgentReport(string $metric, string $since, string $until): array
    {
        return $this->client()
            ->get($this->url('reports/agents'), array_filter(
                compact('metric', 'since', 'until') + ['inbox_id' => $this->inboxId()]
            ))
            ->throw()
            ->json();
    }

    /**
     * Rapport global du compte
     */
    public function getAccountReport(string $metric, string $since, string $until): array
    {
        return $this->client()
            ->get($this->url('reports'), array_filter([
                'type'     => 'account',
                'metric'   => $metric,
                'since'    => $since,
                'until'    => $until,
                'inbox_id' => $this->inboxId(),
            ]))
            ->throw()
            ->json();
    }

    /**
     * Reporting events d'une conversation
     */
    public function getReportingEvents(int $conversationId): array
    {
        return $this->client()
            ->get($this->url("conversations/{$conversationId}/reporting_events"))
            ->throw()
            ->json();
    }

    public function getAccountSummary(string $since, string $until): array
    {
        return $this->client()
            ->get($this->url('reports/summary'), array_filter(
                compact('since', 'until') + ['inbox_id' => $this->inboxId()]
            ))
            ->throw()
            ->json();
    }

    public function getAgentSummary(string $since, string $until): array
    {
        return $this->client()
            ->get($this->url('reports/agents/summary'), array_filter(
                compact('since', 'until') + ['inbox_id' => $this->inboxId()]
            ))
            ->throw()
            ->json();
    }

    // ═══════════════════════════════════════════════
    // CANNED RESPONSES
    // ═══════════════════════════════════════════════

    public function listCannedResponses(?string $search = null): array
    {
        $query = $search ? ['search' => $search] : [];

        return $this->client()
            ->get($this->url('canned_responses'), $query)
            ->throw()
            ->json();
    }

    public function createCannedResponse(string $shortCode, string $content): array
    {
        return $this->client()
            ->post($this->url('canned_responses'), [
                'short_code' => $shortCode,
                'content'    => $content,
            ])
            ->throw()
            ->json();
    }

    public function updateCannedResponse(int $id, string $shortCode, string $content): array
    {
        return $this->client()
            ->put($this->url("canned_responses/{$id}"), [
                'short_code' => $shortCode,
                'content'    => $content,
            ])
            ->throw()
            ->json();
    }

    public function deleteCannedResponse(int $id): void
    {
        $this->client()
            ->delete($this->url("canned_responses/{$id}"))
            ->throw();
    }

    // ═══════════════════════════════════════════════
    // LABELS
    // ═══════════════════════════════════════════════

    public function listAccountLabels(): array
    {
        return $this->client()
            ->get($this->url('labels'))
            ->throw()
            ->json();
    }

    public function createAccountLabel(string $title, ?string $description = null, ?string $color = null, bool $showOnSidebar = true): array
    {
        $payload = ['title' => $title, 'show_on_sidebar' => $showOnSidebar];
        if ($description) $payload['description'] = $description;
        if ($color) $payload['color'] = $color;

        return $this->client()
            ->post($this->url('labels'), $payload)
            ->throw()
            ->json();
    }

    public function getConversationLabels(int $conversationId): array
    {
        return $this->client()
            ->get($this->url("conversations/{$conversationId}/labels"))
            ->throw()
            ->json();
    }

    public function updateConversationLabels(int $conversationId, array $labels): array
    {
        return $this->client()
            ->post($this->url("conversations/{$conversationId}/labels"), [
                'labels' => $labels,
            ])
            ->throw()
            ->json();
    }

    // ═══════════════════════════════════════════════
    // CONTACTS (extended)
    // ═══════════════════════════════════════════════

    public function listContacts(int $page = 1, string $sortBy = '-last_activity_at', bool $includeContactInboxes = false): array
    {
        $query = array_filter([
            'page'     => $page,
            'inbox_id' => $this->inboxId(),
        ]);

        if ($sortBy) {
            $query['sort'] = $sortBy;
        }

        if ($includeContactInboxes) {
            $query['include_contact_inboxes'] = true;
        }

        return $this->client()
            ->get($this->url('contacts'), $query)
            ->throw()
            ->json();
    }

    public function deleteContact(int $contactId): void
    {
        $this->client()
            ->delete($this->url("contacts/{$contactId}"))
            ->throw();
    }

    public function filterContacts(array $payload, int $page = 1): array
    {
        return $this->client()
            ->post($this->url('contacts/filter'), array_merge($payload, ['page' => $page]))
            ->throw()
            ->json();
    }

    public function updateContact(int $contactId, array $data): array
    {
        return $this->client()
            ->put($this->url("contacts/{$contactId}"), $data)
            ->throw()
            ->json();
    }

    public function getContactConversations(int $contactId): array
    {
        return $this->client()
            ->get($this->url("contacts/{$contactId}/conversations"))
            ->throw()
            ->json();
    }

    public function listContactNotes(int $contactId): array
    {
        return $this->client()
            ->get($this->url("contacts/{$contactId}/notes"))
            ->throw()
            ->json();
    }

    public function createContactNote(int $contactId, string $content): array
    {
        return $this->client()
            ->post($this->url("contacts/{$contactId}/notes"), [
                'content' => $content,
            ])
            ->throw()
            ->json();
    }

    public function deleteContactNote(int $contactId, int $noteId): void
    {
        $this->client()
            ->delete($this->url("contacts/{$contactId}/notes/{$noteId}"))
            ->throw();
    }

    // ═══════════════════════════════════════════════
    // NOTIFICATIONS
    // ═══════════════════════════════════════════════

    public function listNotifications(int $page = 1): array
    {
        return $this->client()
            ->get($this->url('notifications'), ['page' => $page])
            ->throw()
            ->json();
    }

    public function markNotificationRead(int $notificationId): void
    {
        $this->client()
            ->patch($this->url("notifications/{$notificationId}"), [
                'read_at' => now()->toIso8601String(),
            ])
            ->throw();
    }

    public function markAllNotificationsRead(): void
    {
        $this->client()
            ->post($this->url('notifications/read_all'))
            ->throw();
    }

    // ═══════════════════════════════════════════════
    // TEAMS
    // ═══════════════════════════════════════════════

    public function listTeams(): array
    {
        return $this->client()
            ->get($this->url('teams'))
            ->throw()
            ->json();
    }

    public function getTeam(int $teamId): array
    {
        return $this->client()
            ->get($this->url("teams/{$teamId}"))
            ->throw()
            ->json();
    }

    public function getTeamMembers(int $teamId): array
    {
        return $this->client()
            ->get($this->url("teams/{$teamId}/team_members"))
            ->throw()
            ->json();
    }

    public function assignTeam(int $conversationId, int $teamId): array
    {
        return $this->client()
            ->post($this->url("conversations/{$conversationId}/assignments"), [
                'team_id' => $teamId,
            ])
            ->throw()
            ->json();
    }

    public function createTeam(string $name, ?string $description = null): array
    {
        $payload = ['name' => $name];
        if ($description) $payload['description'] = $description;

        return $this->client()
            ->post($this->url('teams'), $payload)
            ->throw()
            ->json();
    }

    public function updateTeam(int $teamId, array $data): array
    {
        return $this->client()
            ->patch($this->url("teams/{$teamId}"), $data)
            ->throw()
            ->json();
    }

    public function deleteTeam(int $teamId): void
    {
        $this->client()
            ->delete($this->url("teams/{$teamId}"))
            ->throw();
    }

    public function addTeamMembers(int $teamId, array $userIds): array
    {
        return $this->client()
            ->post($this->url("teams/{$teamId}/team_members"), [
                'user_ids' => $userIds,
            ])
            ->throw()
            ->json();
    }

    public function removeTeamMembers(int $teamId, array $userIds): void
    {
        $this->client()
            ->delete($this->url("teams/{$teamId}/team_members"), [
                'user_ids' => $userIds,
            ])
            ->throw();
    }

    // ═══════════════════════════════════════════════
    // CONVERSATION FILTERS (advanced)
    // ═══════════════════════════════════════════════

    public function filterConversations(array $payload, int $page = 1): array
    {
        return $this->client()
            ->post($this->url('conversations/filter'), array_merge($payload, ['page' => $page]))
            ->throw()
            ->json();
    }

    // ═══════════════════════════════════════════════
    // INBOXES
    // ═══════════════════════════════════════════════

    public function getInbox(int $inboxId): array
    {
        return $this->client()
            ->get($this->url("inboxes/{$inboxId}"))
            ->throw()
            ->json();
    }

    public function updateInbox(int $inboxId, array $data): array
    {
        return $this->client()
            ->patch($this->url("inboxes/{$inboxId}"), $data)
            ->throw()
            ->json();
    }

    public function listInboxes(): array
    {
        return $this->client()
            ->get($this->url('inboxes'))
            ->throw()
            ->json();
    }

    // ═══════════════════════════════════════════════
    // PROFILE / AVAILABILITY
    // ═══════════════════════════════════════════════

    public function getProfile(): array
    {
        return $this->client()
            ->get($this->baseUrl . '/auth/sign_in', [
                'api_access_token' => $this->token,
            ])
            ->throw()
            ->json();
    }

    public function updateAvailability(string $availability): array
    {
        $request = Http::baseUrl($this->baseUrl)
            ->withHeaders(['api_access_token' => $this->token, 'Content-Type' => 'application/json'])
            ->timeout(10);

        if (!app()->isProduction()) {
            $request->withoutVerifying();
        }

        return $request
            ->put('/api/v1/profile', [
                'availability' => $availability,
            ])
            ->throw()
            ->json();
    }

    // ═══════════════════════════════════════════════
    // TYPING INDICATOR
    // ═══════════════════════════════════════════════

    public function toggleTyping(int $conversationId, string $status = 'on'): void
    {
        $this->client()
            ->post($this->url("conversations/{$conversationId}/toggle_typing"), [
                'typing_status' => $status,
            ]);
    }

    // ═══════════════════════════════════════════════
    // MESSAGES (pagination)
    // ═══════════════════════════════════════════════

    /**
     * Supprimer une conversation
     */
    public function deleteConversation(int $conversationId): void
    {
        $this->client()
            ->delete($this->url("conversations/{$conversationId}"))
            ->throw();
    }

    public function deleteMessage(int $conversationId, int $messageId): void
    {
        $this->client()
            ->delete($this->url("conversations/{$conversationId}/messages/{$messageId}"))
            ->throw();
    }

    public function getMessagesBefore(int $conversationId, int $beforeId): array
    {
        return $this->client()
            ->get($this->url("conversations/{$conversationId}/messages"), [
                'before' => $beforeId,
            ])
            ->throw()
            ->json();
    }

    // ═══════════════════════════════════════════════
    // HEALTH CHECK
    // ═══════════════════════════════════════════════

    public function ping(): bool
    {
        try {
            $this->listAgents();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
