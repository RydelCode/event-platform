<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent\Message;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RegistrationCreatedHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(RegistrationCreated $message): void
    {
        $this->logger->info('Registration created message handled.', [
            'registrationId' => $message->getRegistrationId(),
        ]);
    }
}
