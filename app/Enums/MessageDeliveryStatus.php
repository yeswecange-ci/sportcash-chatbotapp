<?php

namespace App\Enums;

enum MessageDeliveryStatus: string
{
    case QUEUED      = 'queued';
    case SENT        = 'sent';
    case DELIVERED   = 'delivered';
    case READ        = 'read';
    case FAILED      = 'failed';
    case UNDELIVERED = 'undelivered';

    public function label(): string
    {
        return match ($this) {
            self::QUEUED      => 'En file',
            self::SENT        => 'Envoye',
            self::DELIVERED   => 'Delivre',
            self::READ        => 'Lu',
            self::FAILED      => 'Echoue',
            self::UNDELIVERED => 'Non delivre',
        };
    }
}
