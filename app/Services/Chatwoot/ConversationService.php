<?php

namespace App\Services\Chatwoot;

use App\DTOs\ConversationDTO;

class ConversationService
{
    public function __construct(
        private ChatwootClient $client
    ) {}

    /**
     * Liste paginée des conversations
     */
    public function list(string $status = 'open', string $assigneeType = 'all', int $page = 1): array
    {
        $inboxId  = (int) config('chatwoot.whatsapp_inbox_id') ?: null;
        $response = $this->client->listConversations($status, $assigneeType, $page, $inboxId);

        return [
            'conversations' => collect($response['data']['payload'] ?? [])
                ->map(fn(array $conv) => ConversationDTO::fromArray($conv))
                ->all(),
            'meta' => $response['data']['meta'] ?? [],
        ];
    }

    /**
     * Conversation + messages pour la vue show
     */
    public function getWithMessages(int $conversationId): array
    {
        $conversation = $this->client->getConversation($conversationId);
        $messages     = $this->client->getMessages($conversationId);

        return [
            'conversation' => ConversationDTO::fromArray($conversation),
            'messages'     => $messages['payload'] ?? [],
            'contact'      => $messages['meta']['contact']['payload'][0] ?? null,
            'assignee'     => $messages['meta']['assignee'] ?? null,
        ];
    }

    /**
     * Polling : messages plus récents que lastMessageId
     */
    public function getNewMessages(int $conversationId, int $lastMessageId): array
    {
        $messages = $this->client->getMessages($conversationId);

        return collect($messages['payload'] ?? [])
            ->filter(fn(array $msg) => $msg['id'] > $lastMessageId)
            ->values()
            ->all();
    }

    public function resolve(int $id): array
    {
        return $this->client->toggleStatus($id, 'resolved');
    }

    public function reopen(int $id): array
    {
        return $this->client->toggleStatus($id, 'open');
    }

    public function assign(int $id, int $agentId): array
    {
        return $this->client->assignConversation($id, $agentId);
    }

    public function getCounts(): array
    {
        return $this->client->getConversationCounts();
    }

    public function search(string $query, int $page = 1): array
    {
        $response = $this->client->searchConversations($query, $page);

        return [
            'conversations' => collect($response['data']['payload'] ?? [])
                ->map(fn(array $conv) => ConversationDTO::fromArray($conv))
                ->all(),
            'meta' => $response['data']['meta'] ?? [],
        ];
    }

    /**
     * Filtrage avance multi-criteres
     */
    public function advancedFilter(array $filters, int $page = 1): array
    {
        $payload = [];

        if (!empty($filters['status'])) {
            $payload[] = [
                'attribute_key'   => 'status',
                'filter_operator' => 'equal_to',
                'values'          => [$filters['status']],
                'query_operator'  => !empty($payload) ? 'AND' : null,
            ];
        }

        if (!empty($filters['assignee_id'])) {
            $payload[] = [
                'attribute_key'   => 'assignee_id',
                'filter_operator' => 'equal_to',
                'values'          => [(int) $filters['assignee_id']],
                'query_operator'  => !empty($payload) ? 'AND' : null,
            ];
        }

        if (!empty($filters['team_id'])) {
            $payload[] = [
                'attribute_key'   => 'team_id',
                'filter_operator' => 'equal_to',
                'values'          => [(int) $filters['team_id']],
                'query_operator'  => !empty($payload) ? 'AND' : null,
            ];
        }

        if (!empty($filters['label'])) {
            $payload[] = [
                'attribute_key'   => 'labels',
                'filter_operator' => 'contains',
                'values'          => [$filters['label']],
                'query_operator'  => !empty($payload) ? 'AND' : null,
            ];
        }

        if (!empty($filters['created_since'])) {
            $payload[] = [
                'attribute_key'   => 'created_at',
                'filter_operator' => 'is_greater_than',
                'values'          => [$filters['created_since']],
                'query_operator'  => !empty($payload) ? 'AND' : null,
            ];
        }

        if (!empty($filters['created_until'])) {
            $payload[] = [
                'attribute_key'   => 'created_at',
                'filter_operator' => 'is_less_than',
                'values'          => [$filters['created_until']],
                'query_operator'  => !empty($payload) ? 'AND' : null,
            ];
        }

        // Filtre inbox
        $inboxId = (int) config('chatwoot.whatsapp_inbox_id') ?: null;
        if ($inboxId) {
            $payload[] = [
                'attribute_key'   => 'inbox_id',
                'filter_operator' => 'equal_to',
                'values'          => [$inboxId],
                'query_operator'  => !empty($payload) ? 'AND' : null,
            ];
        }

        // Fix first item should not have query_operator
        if (!empty($payload)) {
            $payload[0]['query_operator'] = null;
        }

        if (empty($payload)) {
            return $this->list('open', 'all', $page);
        }

        try {
            $response = $this->client->filterConversations(['payload' => $payload], $page);

            return [
                'conversations' => collect($response['payload'] ?? [])
                    ->map(fn(array $conv) => ConversationDTO::fromArray($conv))
                    ->all(),
                'meta' => $response['meta'] ?? [],
            ];
        } catch (\Exception $e) {
            return ['conversations' => [], 'meta' => []];
        }
    }

    /**
     * Filtrage avancé (par label + statut)
     */
    /**
     * Vérifie si la fenêtre WhatsApp 24h est expirée
     * (aucun message entrant du client depuis > 24h)
     */
    public function isWindowExpired(array $messages): bool
    {
        $lastIncoming = collect($messages)
            ->filter(fn(array $msg) => ($msg['message_type'] ?? -1) === 0)
            ->sortByDesc(fn(array $msg) => $msg['created_at'] ?? 0)
            ->first();

        if (!$lastIncoming) {
            return true;
        }

        $createdAt = $lastIncoming['created_at'] ?? 0;
        $timestamp = is_numeric($createdAt) ? $createdAt : strtotime($createdAt);

        return (time() - $timestamp) > 86400;
    }

    public function filterByLabel(string $label, string $status = 'open', int $page = 1): array
    {
        $filters = [
            [
                'attribute_key'   => 'labels',
                'filter_operator' => 'contains',
                'values'          => [$label],
                'query_operator'  => null,
            ],
        ];

        $inboxId = (int) config('chatwoot.whatsapp_inbox_id') ?: null;
        if ($inboxId) {
            $filters[] = [
                'attribute_key'   => 'inbox_id',
                'filter_operator' => 'equal_to',
                'values'          => [$inboxId],
                'query_operator'  => 'AND',
            ];
        }

        if ($status && $status !== 'all') {
            $filters[] = [
                'attribute_key'   => 'status',
                'filter_operator' => 'equal_to',
                'values'          => [$status],
                'query_operator'  => 'AND',
            ];
        }

        try {
            $response = $this->client->filterConversations(['payload' => $filters], $page);

            return [
                'conversations' => collect($response['payload'] ?? [])
                    ->map(fn(array $conv) => ConversationDTO::fromArray($conv))
                    ->all(),
                'meta' => $response['meta'] ?? [],
            ];
        } catch (\Exception $e) {
            return ['conversations' => [], 'meta' => []];
        }
    }
}
