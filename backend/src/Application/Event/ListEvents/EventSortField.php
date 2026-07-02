<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

enum EventSortField: string
{
    case STARTS_AT = 'startsAt';
    case ENDS_AT = 'endsAt';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
