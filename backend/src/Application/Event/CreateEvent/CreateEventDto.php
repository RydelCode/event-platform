<?php

namespace App\Application\Event;

use Symfony\Component\Validator\Constraints as Assert;

class CreateEventDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title;

    #[Assert\Length(max: 5000)]
    public ?string $description;

    #[Assert\NotBlank]
    public string $startsAt;

    #[Assert\NotBlank]
    public string $endsAt;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $location;

    #[Assert\Positive]
    public int $capacity;

    public static function fromArray(array $data): self
    {
        $dto = new self();

        $dto->title = $data['title'] ?? '';
        $dto->description = $data['description'] ?? null;
        $dto->startsAt = $data['startsAt'] ?? '';
        $dto->endsAt = $data['endsAt'] ?? '';
        $dto->location = $data['location'] ?? '';
        $dto->capacity = (int) ($data['capacity'] ?? 0);

        return $dto;
    }
}
