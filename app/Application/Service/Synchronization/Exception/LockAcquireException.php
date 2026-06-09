<?php

declare(strict_types=1);

namespace App\Application\Service\Synchronization\Exception;

use Throwable;

class LockAcquireException extends MutexException
{
    public function __construct(Throwable $e)
    {
        parent::__construct('Lock acquire failed', $e->getCode(), $e);
    }
}
