<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent;

use App\Entity\Registration;
use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RegisterForEventHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private RegistrationRepository $registrationRepository,
    ) {
    }

    public function handle(int $eventId, RegisterForEventDto $dto): ?Registration
    {
        return $this->entityManager->wrapInTransaction(function () use ($eventId, $dto): ?Registration {
            /** @var \App\Entity\Event|null $event */
            $event = $this->eventRepository->findWithPessimisticWriteLock($eventId);

            if (null === $event) {
                return null;
            }

            if ($event->getRegistrations()->count() >= $event->getCapacity()) {
                throw new EventCapacityExceededException($eventId);
            }

            if ($this->registrationRepository->existsForEventAndEmail($event, $dto->attendeeEmail)) {
                throw new DuplicateRegistrationDetectedException($eventId, $dto->attendeeEmail);
            }

            $registration = new Registration();

            $registration->setAttendeeName($dto->attendeeName)
                ->setAttendeeEmail($dto->attendeeEmail)
                ->setEvent($event);

            $this->entityManager->persist($registration);
            $this->entityManager->flush();

            return $registration;
        });
    }
}
