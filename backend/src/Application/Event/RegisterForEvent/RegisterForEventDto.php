<?php

declare(strict_types=1);

namespace App\Application\Event\RegisterForEvent;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterForEventDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $attendeeName;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $attendeeEmail;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();

        $dto->attendeeName = $data['attendeeName'] ?? '';
        $dto->attendeeEmail = $data['attendeeEmail'] ?? '';

        return $dto;
    }
}
