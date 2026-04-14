<?php

namespace App\DTOs;

use App\Enums\MessageType;

class MessageDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $content,
        public readonly int $messageType,
        public readonly bool $private,
        public readonly ?string $senderName,
        public readonly ?string $senderType,
        public readonly ?string $senderThumbnail,
        public readonly string $createdAt,
        public readonly ?int $conversationId,
        public readonly ?int $contentType,
    ) {}

    public static function fromArray(array $data): self
    {
        $sender = $data['sender'] ?? [];

        return new self(
            id: $data['id'],
            content: $data['content'] ?? '',
            messageType: $data['message_type'] ?? 0,
            private: $data['private'] ?? false,
            senderName: $sender['name'] ?? null,
            senderType: $sender['type'] ?? null,
            senderThumbnail: $sender['thumbnail'] ?? null,
            createdAt: isset($data['created_at']) && is_numeric($data['created_at'])
                ? date('Y-m-d H:i:s', (int) $data['created_at'])
                : ($data['created_at'] ?? now()->toDateTimeString()),
            conversationId: $data['conversation_id'] ?? null,
            contentType: $data['content_type'] ?? null,
        );
    }

    public function isOutgoing(): bool
    {
        return $this->messageType === MessageType::OUTGOING->value;
    }

    public function isIncoming(): bool
    {
        return $this->messageType === MessageType::INCOMING->value;
    }

    public function isActivity(): bool
    {
        return $this->messageType === MessageType::ACTIVITY->value;
    }

    public function isPrivateNote(): bool
    {
        return $this->private;
    }

    public function bubbleClass(): string
    {
        if ($this->private) {
            return 'bg-yellow-50 border-yellow-200';
        }

        return $this->isOutgoing()
            ? 'bg-blue-500 text-white'
            : 'bg-gray-100 text-gray-900';
    }
}
