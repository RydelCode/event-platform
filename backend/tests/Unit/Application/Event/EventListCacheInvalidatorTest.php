<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Event;

use App\Application\Event\EventListCacheInvalidator;
use App\Infrastructure\Cache\EventCache;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class EventListCacheInvalidatorTest extends TestCase
{
    public function testInvalidateSwallowsInvalidArgumentExceptionAndLogsWarning(): void
    {
        $exception = new class extends \Exception implements InvalidArgumentException {
        };

        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache
            ->expects(self::once())
            ->method('invalidateTags')
            ->with([EventCache::LIST_TAG])
            ->willThrowException($exception)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with('Failed to invalidate event list cache.', [
                'exception' => $exception,
                'tag' => EventCache::LIST_TAG,
            ])
        ;

        (new EventListCacheInvalidator($cache, $logger))->invalidate();
    }
}
