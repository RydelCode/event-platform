<?php

declare(strict_types=1);

namespace App\Application\Event\ListEvents;

interface ListEventsHandlerInterface
{
    public function handle(EventListQueryDto $query): EventListResponseDto;
}
