<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

use App\Application\Common\Pagination\PaginationMetaDto;
use App\Repository\EventRepository;

final readonly class ListEventsHandler implements ListEventsHandlerInterface
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    public function handle(EventListQueryDto $query): EventListResponseDto
    {
        $items = $this->eventRepository->findList(
            page: $query->page,
            limit: $query->limit,
            status: $query->getStatus(),
            startsAfter: $query->getStartsAfter(),
            sort: $query->getSort(),
            direction: $query->getDirection(),
        );

        $total = $this->eventRepository->countList(
            $query->getStatus(),
            $query->getStartsAfter(),
        );

        return new EventListResponseDto(
            items: $items,
            meta: new PaginationMetaDto(
                page: $query->page,
                limit: $query->limit,
                total: $total,
            ),
        );
    }
}
