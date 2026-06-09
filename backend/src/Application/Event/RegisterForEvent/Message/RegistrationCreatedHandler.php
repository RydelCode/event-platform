<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent\Message;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RegistrationCreatedHandler
{
    public function __invoke(RegistrationCreated $message): void
    {
        var_dump($message->getRegistrationId());
    }
}
