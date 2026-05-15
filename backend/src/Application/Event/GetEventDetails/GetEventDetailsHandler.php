<?php

namespace App\Application\Event\GetEventDetails;

use App\Repository\EventRepository;

final readonly class GetEventDetailsHandler
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {}

    /**
     * @return GetEventDetailsDto|null
     */
    public function handle(int $id) : ?GetEventDetailsDto
    {
        $event = $this->eventRepository->find($id);

        if ($event === null) {
            return null;
        }

        return GetEventDetailsDto::fromEntity($event);
    }
}
