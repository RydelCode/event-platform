<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent;

use App\Entity\Registration;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RegisterForEventHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
    ) {
    }

    public function handle(int $eventId, RegisterForEventDto $dto): ?Registration
    {
        $event = $this->eventRepository->find($eventId);

        if (null === $event) {
            return null;
        }

        $registration = new Registration();

        $registration->setAttendeeName($dto->attendeeName)
            ->setAttendeeEmail($dto->attendeeEmail)
            ->setEvent($event);

        $this->entityManager->persist($registration);
        $this->entityManager->flush();

        return $registration;
    }
}
