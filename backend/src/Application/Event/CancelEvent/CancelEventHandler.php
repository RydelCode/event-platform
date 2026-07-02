<?php

declare(strict_types=1);

namespace App\Application\Event\CancelEvent;

use App\Application\Event\EventListCacheInvalidator;
use App\Application\Event\EventTransitionHandler;
use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CancelEventHandler implements EventTransitionHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private EventListCacheInvalidator $eventListCacheInvalidator,
    ) {
    }

    public function handle(int $eventId): ?Event
    {
        $event = $this->eventRepository->getById($eventId);

        if (null === $event) {
            return null;
        }

        $event->cancel();

        $this->entityManager->flush();

        $this->eventListCacheInvalidator->invalidate();

        return $event;
    }
}
