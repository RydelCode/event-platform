<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

use App\Entity\Event;
use App\Infrastructure\Cache\EventCache;
use App\Repository\EventRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class ListEventsHandler
{
    public function __construct(
        private EventRepository $eventRepository,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @return EventListItemDto[]
     */
    public function handle(): array
    {
        return $this->cache->get(EventCache::LIST_KEY, function (ItemInterface $item): array {
            $item->expiresAfter(EventCache::LIST_TTL);

            return array_map(
                static fn (Event $event): EventListItemDto => EventListItemDto::fromEntity($event),
                $this->eventRepository->findBy([], ['startsAt' => 'ASC']),
            );
        });
    }
}
