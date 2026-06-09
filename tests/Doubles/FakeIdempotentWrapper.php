<?php

declare(strict_types=1);

namespace Tests\Doubles;

use App\Application\Service\IdempotentWrapper;

class FakeIdempotentWrapper implements IdempotentWrapper
{
    /** @var array<string, mixed> */
    private array $cache = [];

    public function wrap(
        string $idempotencyKey,
        callable $callback,
    ): mixed {
        if (array_key_exists($idempotencyKey, $this->cache)) {
            return $this->cache[$idempotencyKey];
        }

        $result = $callback();

        $this->cache[$idempotencyKey] = $result;

        return $result;
    }
}
