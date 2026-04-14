<?php

namespace App\DTOs;

class ConversationDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly string $contactName,
        public readonly ?string $contactPhone,
        public readonly ?string $contactThumbnail,
        public readonly ?string $lastMessage,
        public readonly ?string $lastMessageAt,
        public readonly int $unreadCount,
        public readonly ?string $assigneeName,
        public readonly ?int $assigneeId,
        public readonly ?string $channel,
        public readonly ?string $priority,
        public readonly array $labels,
        public readonly string $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        $sender   = $data['meta']['sender'] ?? [];
        $assignee = $data['meta']['assignee'] ?? [];
        $lastMsg  = $data['last_non_activity_message'] ?? $data['messages'][0] ?? [];

        return new self(
            id: $data['id'],
            status: $data['status'] ?? 'open',
            contactName: $sender['name'] ?? 'Inconnu',
            contactPhone: $sender['phone_number'] ?? null,
            contactThumbnail: $sender['thumbnail'] ?? null,
            lastMessage: $lastMsg['content'] ?? null,
            lastMessageAt: isset($lastMsg['created_at'])
                ? self::formatTimestamp($lastMsg['created_at'])
                : null,
            unreadCount: $data['unread_count'] ?? 0,
            assigneeName: $assignee['name'] ?? null,
            assigneeId: $assignee['id'] ?? null,
            channel: $data['meta']['channel'] ?? null,
            priority: $data['priority'] ?? null,
            labels: $data['labels'] ?? [],
            createdAt: isset($data['created_at'])
                ? self::formatTimestamp($data['created_at'])
                : now()->toDateTimeString(),
        );
    }

    /**
     * Chatwoot renvoie des timestamps UNIX, on les convertit
     */
    private static function formatTimestamp(mixed $ts): string
    {
        if (is_numeric($ts)) {
            return date('Y-m-d H:i:s', (int) $ts);
        }
        return (string) $ts;
    }

    /**
     * Temps relatif pour l'affichage (il y a X min)
     */
    public function timeAgo(): string
    {
        if (!$this->lastMessageAt) {
            return '';
        }

        return \Carbon\Carbon::parse($this->lastMessageAt)->diffForHumans();
    }

    /**
     * Label français du statut
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'open'     => 'Ouvert',
            'pending'  => 'En attente',
            'resolved' => 'Résolu',
            'snoozed'  => 'Reporté',
            default    => ucfirst($this->status),
        };
    }

    /**
     * Classe CSS selon le statut
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'open'     => 'bg-blue-50 text-blue-700',
            'pending'  => 'bg-amber-50 text-amber-700',
            'resolved' => 'bg-green-50 text-green-700',
            'snoozed'  => 'bg-gray-100 text-gray-600',
            default    => 'bg-gray-100 text-gray-600',
        };
    }
}
