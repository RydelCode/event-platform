<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

use App\Domain\Event\EventStatus;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class EventListQueryDto
{
    public function __construct(
        #[Assert\Positive]
        public int $page = 1,

        #[Assert\Range(min: 1, max: 50)]
        public int $limit = 10,

        #[Assert\Choice(callback: [EventStatus::class, 'values'])]
        public ?string $status = null,

        #[Assert\Date]
        public ?string $startsAfter = null,

        #[Assert\Choice(callback: [EventSortField::class, 'values'])]
        public string $sort = EventSortField::STARTS_AT->value,

        #[Assert\Choice(callback: [SortDirection::class, 'values'])]
        public string $direction = SortDirection::ASC->value,
    ) {
    }

    public function getStatus(): ?EventStatus
    {
        return null !== $this->status ? EventStatus::from($this->status) : null;
    }

    public function getSort(): EventSortField
    {
        return EventSortField::from($this->sort);
    }

    public function getDirection(): SortDirection
    {
        return SortDirection::from($this->direction);
    }

    public function getStartsAfter(): ?\DateTimeImmutable
    {
        if (null !== $this->startsAfter) {
            return \DateTimeImmutable::createFromFormat('!Y-m-d', $this->startsAfter) ?: null;
        }

        return null;
    }
}
