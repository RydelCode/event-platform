<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent\Message;

final readonly class RegistrationCreated
{
    public function __construct(
        private int $registrationId,
    ) {
    }

    public function getRegistrationId(): int
    {
        return $this->registrationId;
    }
}
