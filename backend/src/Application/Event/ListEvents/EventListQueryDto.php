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

        #[Assert\DateTime(format: 'Y-m-d\TH:i')]
        public ?string $startsAfter = null,

        #[Assert\Choice(choices: ['startsAt', 'endsAt'])]
        public string $sort = 'startsAt',

        #[Assert\Choice(choices: ['asc', 'desc'])]
        public string $order = 'asc',
    ) {
    }

    public function getStatus(): ?EventStatus
    {
        return null !== $this->status ? EventStatus::from($this->status) : null;
    }

    public function getStartsAfter(): ?\DateTimeImmutable
    {
        return null !== $this->startsAfter ? new \DateTimeImmutable($this->startsAfter) : null;
    }
}
