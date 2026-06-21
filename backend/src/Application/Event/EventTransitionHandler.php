<?php

declare(strict_types=1);

namespace App\Application\Event;

use App\Entity\Event;

interface EventTransitionHandler
{
    public function handle(int $eventId): ?Event;
}
