<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent;

final class DuplicateRegistrationDetectedException extends \RuntimeException
{
    public function __construct(int $eventId, string $attendeeEmail)
    {
        parent::__construct(
            sprintf('Attendee with email %s already registered for event %d.', $attendeeEmail, $eventId)
        );
    }
}
