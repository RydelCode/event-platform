<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

use App\Application\Common\Pagination\PaginationMetaDto;

final readonly class EventListResponseDto
{
    /**
     * @param EventListItemDto[] $items
     */
    public function __construct(
        public array $items,
        public PaginationMetaDto $meta,
    ) {
    }
}
