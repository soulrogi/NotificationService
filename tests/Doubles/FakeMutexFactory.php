<?php

declare(strict_types=1);

namespace Tests\Doubles;

use App\Application\Service\Synchronization\Mutex;
use App\Application\Service\Synchronization\MutexFactory;

class FakeMutexFactory implements MutexFactory
{
    public function create(
        string $name,
        int $timeout = 0,
    ): Mutex {
        return new FakeMutex();
    }
}
