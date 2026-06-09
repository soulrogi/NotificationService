<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Synchronization\Driver;

use App\Application\Service\Synchronization\Exception\LockAcquireException;
use App\Application\Service\Synchronization\Exception\LockReleaseException;
use App\Application\Service\Synchronization\Exception\TimeoutException;
use App\Application\Service\Synchronization\Mutex;
use Illuminate\Cache\RedisLock;
use Throwable;

readonly class RedisMutex implements Mutex
{
    public function __construct(
        private RedisLock $lock,
    ) {
    }

    public function lock(): void
    {
        try {
            $isLocked = $this->lock->acquire();
        } catch (Throwable $e) {
            throw new LockAcquireException($e);
        }

        if (!$isLocked) {
            throw new TimeoutException();
        }
    }

    public function unlock(): void
    {
        try {
            $this->lock->release();
        } catch (Throwable $e) {
            throw new LockReleaseException($e);
        }
    }
}
