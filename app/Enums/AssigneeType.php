<?php

namespace App\Enums;

enum AssigneeType: string
{
    case ME         = 'me';
    case UNASSIGNED = 'unassigned';
    case ALL        = 'all';
    case ASSIGNED   = 'assigned';

    public function label(): string
    {
        return match ($this) {
            self::ME         => 'Mes conversations',
            self::UNASSIGNED => 'Non assignées',
            self::ALL        => 'Toutes',
            self::ASSIGNED   => 'Assignées',
        };
    }
}
