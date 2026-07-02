<?php

declare(strict_types=1);

namespace App\Application\Event;

use App\Infrastructure\Cache\EventCache;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final readonly class EventListCacheInvalidator
{
    public function __construct(
        private TagAwareCacheInterface $cache,
        private LoggerInterface $logger,
    ) {
    }

    public function invalidate(): void
    {
        try {
            $this->cache->invalidateTags([EventCache::LIST_TAG]);
        } catch (InvalidArgumentException $exception) {
            $this->logger->warning('Failed to invalidate event list cache.', [
                'exception' => $exception,
                'tag' => EventCache::LIST_TAG,
            ]);
        }
    }
}
