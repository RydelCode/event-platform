<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

final class EventCache
{
    public const string LIST_TAG = 'events.list';
    public const string LIST_TAG_PREFIX = 'events.list.';
    public const int LIST_TTL = 60;
}
