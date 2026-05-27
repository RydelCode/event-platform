<?php

declare(strict_types=1);

namespace App\Application\Event\GetEventDetails;

use App\Repository\EventRepository;

final readonly class GetEventDetailsHandler
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    public function handle(int $id): ?GetEventDetailsDto
    {
        $event = $this->eventRepository->find($id);

        if (null === $event) {
            return null;
        }

        return GetEventDetailsDto::fromEntity($event);
    }
}
