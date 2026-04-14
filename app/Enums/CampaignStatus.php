<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case DRAFT     = 'draft';
    case ACTIVE    = 'active';
    case COMPLETED = 'completed';
    case PAUSED    = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Brouillon',
            self::ACTIVE    => 'Active',
            self::COMPLETED => 'Terminee',
            self::PAUSED    => 'En pause',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT     => 'gray',
            self::ACTIVE    => 'blue',
            self::COMPLETED => 'green',
            self::PAUSED    => 'yellow',
        };
    }
}
