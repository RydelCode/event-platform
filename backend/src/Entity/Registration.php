<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RegistrationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $attendeeName;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private Event $event;

    #[ORM\Column(length: 255)]
    private string $attendeeEmail;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new \LogicException('Registration ID is not set');
        }

        return $this->id;
    }

    public function getAttendeeName(): string
    {
        return $this->attendeeName;
    }

    public function setAttendeeName(string $attendeeName): static
    {
        $this->attendeeName = $attendeeName;

        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getAttendeeEmail(): string
    {
        return $this->attendeeEmail;
    }

    public function setAttendeeEmail(string $attendeeEmail): static
    {
        $this->attendeeEmail = $attendeeEmail;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
