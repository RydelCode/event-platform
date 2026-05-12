<?php

namespace App\Application\Event\ListEvents;

use App\Entity\Event;

final readonly class EventListItemDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public string $startsAt,
        public string $endsAt,
        public string $location,
        public int $capacity
    ) {}

    public static function fromEntity(Event $event): self
    {
        return new self(
            $event->getId(),
            $event->getTitle(),
            $event->getDescription(),
            $event->getStartsAt()->format(DATE_ATOM),
            $event->getEndsAt()->format(DATE_ATOM),
            $event->getLocation(),
            $event->getCapacity()
        );
    }
}
