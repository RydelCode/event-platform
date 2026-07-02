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
            $this->cacheKey($query),
            function (ItemInterface $item) use ($query): EventListResponseDto {
                $item->expiresAfter(EventCache::LIST_TTL);
                $item->tag(EventCache::LIST_TAG);

                return $this->inner->handle($query);
            }
        );
    }

    private function cacheKey(EventListQueryDto $query): string
    {
        return EventCache::LIST_TAG_PREFIX.md5(json_encode([
            'page' => $query->page,
            'limit' => $query->limit,
            'status' => $query->status,
            'startsAfter' => $query->startsAfter,
            'sort' => $query->sort,
            'direction' => $query->direction,
        ], JSON_THROW_ON_ERROR));
    }
}
