<?php

declare(strict_types=1);

namespace App\Application\Common\DateTime;

final readonly class DateTimeParser
{
    public const string HTML_LOCAL_DATE_TIME_FORMAT = 'Y-m-d\TH:i';

    public function parseHtmlLocalDateTime(string $dateTime, string $fieldName): \DateTimeImmutable
    {
        return $this->parse($dateTime, self::HTML_LOCAL_DATE_TIME_FORMAT, $fieldName);
    }

    public function parse(string $dateTime, string $format, string $fieldName): \DateTimeImmutable
    {
        $parsedDateTime = \DateTimeImmutable::createFromFormat('!'.$format, $dateTime);
        $errors = \DateTimeImmutable::getLastErrors();

        if (false === $parsedDateTime || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new \InvalidArgumentException(sprintf('Invalid "%s" date time format.', $fieldName));
        }

        return $parsedDateTime;
    }
}
