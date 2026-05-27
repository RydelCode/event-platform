<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

use App\Repository\EventRepository;

final readonly class ListEventsHandler
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    /**
     * @return EventListItemDto[]
     */
    public function handle(): array
    {
        $events = $this->eventRepository->findBy([], ['startsAt' => 'ASC']);

        return array_map(
            static fn ($event) => EventListItemDto::fromEntity($event),
            $events
        );
    }
}
