<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

use App\Domain\Event\EventStatus;

final readonly class EventListItemDto
{
    public string $startsAt;
    public string $endsAt;

    public function __construct(
        public int $id,
        public EventStatus $status,
        public string $title,
        public ?string $description,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        public string $location,
        public int $capacity,
    ) {
        $this->startsAt = $startsAt->format('Y-m-d\TH:i');
        $this->endsAt = $endsAt->format('Y-m-d\TH:i');
    }
}
