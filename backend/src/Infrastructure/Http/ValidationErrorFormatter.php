<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final readonly class ValidationErrorFormatter
{
    public function format(ConstraintViolationListInterface $errors): array
    {
        $formattedErrors = [];

        foreach ($errors as $error) {
            $formattedErrors[$error->getPropertyPath()][] = $error->getMessage();
        }

        return $formattedErrors;
    }
}
