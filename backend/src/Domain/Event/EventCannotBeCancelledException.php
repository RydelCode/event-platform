<?php

declare(strict_types=1);

namespace App\Domain\Event;

final class EventCannotBeCancelledException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Event cannot be cancelled.');
    }
}
