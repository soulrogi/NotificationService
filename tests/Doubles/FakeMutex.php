<?php

declare(strict_types=1);

namespace Tests\Doubles;

use App\Application\Service\Synchronization\Mutex;

class FakeMutex implements Mutex
{
    public function lock(): void
    {
    }

    public function unlock(): void
    {
    }
}
