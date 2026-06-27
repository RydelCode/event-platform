<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Common\DateTime;

use App\Application\Common\DateTime\DateTimeParser;
use PHPUnit\Framework\TestCase;

final class DateTimeParserTest extends TestCase
{
    public function testParseHtmlLocalDateTimeReturnsDateTime(): void
    {
        $dateTime = (new DateTimeParser())->parseHtmlLocalDateTime('2026-06-01T10:30', 'startsAt');

        self::assertSame('2026-06-01 10:30:00', $dateTime->format('Y-m-d H:i:s'));
    }

    public function testParseHtmlLocalDateTimeRejectsInvalidDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid "startsAt" date time format.');

        (new DateTimeParser())->parseHtmlLocalDateTime('2026-02-31T10:00', 'startsAt');
    }

    public function testParseHtmlLocalDateTimeRejectsInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid "endsAt" date time format.');

        (new DateTimeParser())->parseHtmlLocalDateTime('2026-06-01 10:30', 'endsAt');
    }
}
