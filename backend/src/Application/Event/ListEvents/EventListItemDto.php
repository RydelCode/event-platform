<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

use App\Domain\Event\EventStatus;

final readonly class EventListItemDto
{
    public function __construct(
        public int $id,
        public string $title,
        public EventStatus $status,
        public ?string $description,
        public string $location,
        public int $capacity,
    ) {
    }
}
