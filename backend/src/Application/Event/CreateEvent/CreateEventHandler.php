<?php

declare(strict_types=1);

namespace App\Application\Event\CreateEvent;

use App\Application\Common\DateTime\DateTimeParser;
use App\Application\Event\EventListCacheInvalidator;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CreateEventHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventListCacheInvalidator $eventListCacheInvalidator,
        private DateTimeParser $dateTimeParser,
    ) {
    }

    public function handle(CreateEventDto $dto): Event
    {
        $event = $this->createEvent($dto);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->eventListCacheInvalidator->invalidate();

        return $event;
    }

    private function createEvent(CreateEventDto $dto): Event
    {
        $event = new Event();

        $event->setTitle($dto->title);
        $event->setDescription($dto->description);
        $event->setStartsAt($this->dateTimeParser->parseHtmlLocalDateTime($dto->startsAt, 'startsAt'));
        $event->setEndsAt($this->dateTimeParser->parseHtmlLocalDateTime($dto->endsAt, 'endsAt'));
        $event->setLocation($dto->location);
        $event->setCapacity($dto->capacity);

        return $event;
    }
}
