<?php

declare(strict_types=1);

namespace App\Application\Service\Synchronization;

interface Mutex
{
    public function lock(): void;

    public function unlock(): void;
}
