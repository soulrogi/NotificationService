<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Synchronization\Driver;

use App\Application\Service\Synchronization\Mutex;
use App\Application\Service\Synchronization\MutexFactory;
use Illuminate\Cache\RedisLock;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Redis\RedisManager;
use InvalidArgumentException;

readonly class RedisMutexFactory implements MutexFactory
{
    /** @var Connection&PhpRedisConnection */
    private Connection $redis;

    public function __construct(
        RedisManager $rm,
    ) {
        /** @var PhpRedisConnection $connection */
        $connection = $rm->connection();
        $this->redis = $connection;
    }

    public function create(
        string $name,
        int $timeout = 0,
    ): Mutex {
        if (strlen($name) > 64) {
            throw new InvalidArgumentException('The maximum length of the lock name is 64 characters.');
        }

        return new RedisMutex(
            lock: new RedisLock(
                redis: $this->redis,
                name: $name,
                seconds: $timeout,
            ),
        );
    }
}
