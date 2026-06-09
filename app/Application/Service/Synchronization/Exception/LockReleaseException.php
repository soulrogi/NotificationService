<?php

declare(strict_types=1);

namespace App\Application\Service\Synchronization\Exception;

use Throwable;

class LockReleaseException extends MutexException
{
    public function __construct(Throwable $e)
    {
        parent::__construct('Lock release failed', $e->getCode(), $e);
    }

}
