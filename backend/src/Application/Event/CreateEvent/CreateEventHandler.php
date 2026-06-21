<?php

declare(strict_types=1);

namespace App\Application\Event\CreateEvent;

use App\Entity\Event;
use App\Infrastructure\Cache\EventCache;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;

final readonly class CreateEventHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache,
    ) {
    }

    public function handle(CreateEventDto $dto): Event
    {
        $event = new Event();

        $event->setTitle($dto->title);
        $event->setDescription($dto->description);
        $event->setStartsAt(new \DateTimeImmutable($dto->startsAt));
        $event->setEndsAt(new \DateTimeImmutable($dto->endsAt));
        $event->setLocation($dto->location);
        $event->setCapacity($dto->capacity);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->cache->delete(EventCache::LIST_KEY);

        return $event;
    }
}
