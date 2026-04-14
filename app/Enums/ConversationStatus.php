<?php

namespace App\Enums;

enum ConversationStatus: string
{
    case OPEN     = 'open';
    case PENDING  = 'pending';
    case RESOLVED = 'resolved';
    case SNOOZED  = 'snoozed';
    case ALL      = 'all';

    public function label(): string
    {
        return match ($this) {
            self::OPEN     => 'Ouvert',
            self::PENDING  => 'En attente',
            self::RESOLVED => 'Résolu',
            self::SNOOZED  => 'Snooze',
            self::ALL      => 'Tout',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN     => 'green',
            self::PENDING  => 'yellow',
            self::RESOLVED => 'gray',
            self::SNOOZED  => 'blue',
            self::ALL      => 'indigo',
        };
    }
}
