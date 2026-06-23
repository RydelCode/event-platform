<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final readonly class ValidationErrorFormatter
{
    /**
     * @return array<string, string[]>
     */
    public function format(ConstraintViolationListInterface $errors): array
    {
        $formattedErrors = [];

        foreach ($errors as $error) {
            $formattedErrors[$error->getPropertyPath()][] = (string) $error->getMessage();
        }

        return $formattedErrors;
    }
}
