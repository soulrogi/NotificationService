<?php

declare(strict_types=1);

namespace App\Application\Service\Synchronization\Exception;

class TimeoutException extends MutexException
{
    protected $message = 'Lock is not acquired';
}
