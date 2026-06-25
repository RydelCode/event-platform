<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Application\Event\ListEvents\EventListQueryDto;

final class EventCache
{
    public const string LIST_TAG = 'events.list';
    public const int LIST_TTL = 60;

    public static function listKey(EventListQueryDto $query): string
    {
        return self::LIST_TAG.md5(json_encode([
            'page' => $query->page,
            'limit' => $query->limit,
            'status' => $query->status,
            'startsAfter' => $query->startsAfter,
            'sort' => $query->sort,
            'order' => $query->order,
        ]));
    }
}
