<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Event\EventCannotBeCancelledException;
use App\Domain\Event\EventCannotBeCompletedException;
use App\Domain\Event\EventCannotBePublishedException;
use App\Domain\Event\EventStatus;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: EventStatus::class)]
    private EventStatus $status;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private \DateTimeImmutable $startsAt;

    #[ORM\Column]
    private \DateTimeImmutable $endsAt;

    #[ORM\Column(length: 255)]
    private string $location;

    #[ORM\Column]
    private int $capacity;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Registration>
     */
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $registrations;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->registrations = new ArrayCollection();
        $this->status = EventStatus::DRAFT;
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new \LogicException('Event ID is not set');
        }

        return $this->id;
    }

    public function getStatus(): EventStatus
    {
        return $this->status;
    }

    public function publish(): void
    {
        if (EventStatus::DRAFT !== $this->status) {
            throw new EventCannotBePublishedException();
        }

        $this->status = EventStatus::PUBLISHED;
    }

    public function cancel(): void
    {
        if (EventStatus::COMPLETED === $this->status
        || EventStatus::CANCELLED === $this->status) {
            throw new EventCannotBeCancelledException();
        }

        $this->status = EventStatus::CANCELLED;
    }

    public function complete(): void
    {
        if (EventStatus::PUBLISHED !== $this->status) {
            throw new EventCannotBeCompletedException();
        }

        $this->status = EventStatus::COMPLETED;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartsAt(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): \DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

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

    /**
     * @return Collection<int, Registration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setEvent($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): static
    {
        $this->registrations->removeElement($registration);

        return $this;
    }
}
