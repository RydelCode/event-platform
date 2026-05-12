<?php

namespace App\Application\Event;

use App\Application\Event\CreateEventDto;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CreateEventHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function handle(CreateEventDto $dto) : Event
    {
        $event = new Event();

        $event->setTitle($dto->title);
        $event->setDescription($dto->description);
        $event->setStartsAt(new \DateTimeImmutable($dto->startsAt));
        $event->setEndsAt(new \DateTimeImmutable($dto->endsAt));
        $event->setLocation($dto->location);
        $event->setCapacity($dto->capacity);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }
}
