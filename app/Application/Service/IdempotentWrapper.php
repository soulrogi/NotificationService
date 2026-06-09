<?php

declare(strict_types=1);

namespace App\Application\Service;

interface IdempotentWrapper
{
    public function wrap(
        string $idempotencyKey,
        callable $callback,
    ): mixed;
}
