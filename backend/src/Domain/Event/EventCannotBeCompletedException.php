<?php

declare(strict_types=1);

namespace App\Domain\Event;

final class EventCannotBeCompletedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Event cannot be completed.');
    }
}
