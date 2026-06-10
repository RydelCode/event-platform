<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent\MessageHandler;

use App\Application\Event\RegisterForEvent\Message\RegistrationCreated;
use App\Repository\RegistrationRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final readonly class RegistrationCreatedHandler
{
    public function __construct(
        private RegistrationRepository $registrationRepository,
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(RegistrationCreated $message): void
    {
        $registration = $this->registrationRepository->getById($message->getRegistrationId());

        if (!$registration) {
            throw new \RuntimeException(sprintf('Registration ID: %d not found', $message->getRegistrationId()));
        }

        $email = (new Email())
            ->from('no-reply@event-platform.local')
            ->to($registration->getAttendeeEmail())
            ->subject('Registration confirmed')
            ->text(sprintf(
                'Hello %s, your registration for event "%s" has been confirmed.',
                $registration->getAttendeeName(),
                $registration->getEvent()->getTitle(),
            ));

        $this->mailer->send($email);
    }
}
