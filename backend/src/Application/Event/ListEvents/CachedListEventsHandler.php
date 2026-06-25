<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

use App\Infrastructure\Cache\EventCache;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final readonly class CachedListEventsHandler implements ListEventsHandlerInterface
{
    public function __construct(
        private ListEventsHandlerInterface $inner,
        private TagAwareCacheInterface $cache,
    ) {
    }

    public function handle(EventListQueryDto $query): EventListResponseDto
    {
        return $this->cache->get(
            EventCache::listKey($query),
            function (ItemInterface $item) use ($query): EventListResponseDto {
                $item->expiresAfter(EventCache::LIST_TTL);
                $item->tag(EventCache::LIST_TAG);

                return $this->inner->handle($query);
            }
        );
    }
}
