<?php

declare(strict_types=1);

namespace App\Application\Event\CompleteEvent;

use App\Application\Event\EventTransitionHandler;
use App\Entity\Event;
use App\Infrastructure\Cache\EventCache;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;

final readonly class CompleteEventHandler implements EventTransitionHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private CacheInterface $cache,
    ) {
    }

    public function handle(int $eventId): ?Event
    {
        $event = $this->eventRepository->getById($eventId);

        if (null === $event) {
            return null;
        }

        $event->complete();

        $this->entityManager->flush();

        $this->cache->delete(EventCache::LIST_KEY);

        return $event;
    }
}
