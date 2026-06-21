<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent;

final class EventNotPublishedException extends \RuntimeException
{
    public function __construct(int $eventId)
    {
        parent::__construct(
            sprintf('Event %d is not published.', $eventId)
        );
    }
}
