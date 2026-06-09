<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
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
