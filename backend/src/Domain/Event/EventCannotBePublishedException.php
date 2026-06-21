<?php

declare(strict_types=1);

namespace App\Domain\Event;

final class EventCannotBePublishedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Event cannot be published.');
    }
}
