<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

final class EventCache
{
    public const string LIST_KEY = 'events_list';
    public const int LIST_TTL = 60;
}
