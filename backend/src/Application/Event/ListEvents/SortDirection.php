<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

enum SortDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
