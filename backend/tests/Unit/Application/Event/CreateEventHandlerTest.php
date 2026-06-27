<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Event;

use App\Application\Common\DateTime\DateTimeParser;
use App\Application\Event\CreateEvent\CreateEventDto;
use App\Application\Event\CreateEvent\CreateEventHandler;
use App\Application\Event\EventListCacheInvalidator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class CreateEventHandlerTest extends TestCase
{
    public function testHandleRejectsInvalidStartDate(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache->expects(self::never())->method('invalidateTags');

        $handler = new CreateEventHandler(
            $entityManager,
            new EventListCacheInvalidator($cache, new NullLogger()),
            new DateTimeParser(),
        );

        $dto = $this->createDto();
        $dto->startsAt = '2026-02-31T10:00';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid "startsAt" date time format.');

        $handler->handle($dto);
    }

    public function testHandleRejectsInvalidEndDate(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache->expects(self::never())->method('invalidateTags');

        $handler = new CreateEventHandler(
            $entityManager,
            new EventListCacheInvalidator($cache, new NullLogger()),
            new DateTimeParser(),
        );

        $dto = $this->createDto();
        $dto->endsAt = 'not-a-date';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid "endsAt" date time format.');

        $handler->handle($dto);
    }

    private function createDto(): CreateEventDto
    {
        return CreateEventDto::fromArray([
            'title' => 'Test Event',
            'description' => 'Created from unit test',
            'startsAt' => '2026-06-01T10:00',
            'endsAt' => '2026-06-01T15:00',
            'location' => 'Warsaw',
            'capacity' => 100,
        ]);
    }
}
