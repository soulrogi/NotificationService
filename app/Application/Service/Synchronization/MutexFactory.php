<?php

declare(strict_types=1);

namespace App\Application\Service\Synchronization;

interface MutexFactory
{
    public function create(
        string $name,
        int $timeout = 0,
    ): Mutex;
}
