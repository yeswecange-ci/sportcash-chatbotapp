<?php

namespace App\Enums;

enum MessageType: int
{
    case INCOMING = 0;  // Client → Agent
    case OUTGOING = 1;  // Agent → Client
    case ACTIVITY = 2;  // Système (assignation, résolution...)

    public function label(): string
    {
        return match ($this) {
            self::INCOMING => 'Entrant',
            self::OUTGOING => 'Sortant',
            self::ACTIVITY => 'Activité',
        };
    }

    public function isFromCustomer(): bool
    {
        return $this === self::INCOMING;
    }
}
