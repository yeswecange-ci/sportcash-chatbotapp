<?php

namespace App\DTOs;

class ContactDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $phoneNumber,
        public readonly ?string $thumbnail,
        public readonly ?string $identifier,
        public readonly array $customAttributes,
        public readonly string $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? 'Inconnu',
            email: $data['email'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            thumbnail: $data['thumbnail'] ?? null,
            identifier: $data['identifier'] ?? null,
            customAttributes: $data['custom_attributes'] ?? [],
            createdAt: isset($data['created_at']) && is_numeric($data['created_at'])
                ? date('Y-m-d H:i:s', (int) $data['created_at'])
                : ($data['created_at'] ?? now()->toDateTimeString()),
        );
    }

    public function displayName(): string
    {
        return $this->name !== 'Inconnu' ? $this->name : ($this->phoneNumber ?? 'Contact sans nom');
    }

    public function initials(): string
    {
        $parts = explode(' ', $this->name);
        $initials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials ?: '?';
    }
}
