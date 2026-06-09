<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Application\Service\IdempotentWrapper;
use Exception;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Redis\RedisManager;
use Throwable;

class IdempotentWrapperImpl implements IdempotentWrapper
{
    private int $resultTtl24hours = 86400;
    private int $waitTimeout = 10;

    /** @var Connection&PhpRedisConnection */
    private Connection $redis;

    public function __construct(
        RedisManager $rm,
    ) {
        /** @var PhpRedisConnection $connection */
        $connection = $rm->connection();
        $this->redis = $connection;
    }

    public function wrap(
        string $idempotencyKey,
        callable $callback,
    ): mixed {
        $lockKey = 'idem:lock:' . $idempotencyKey;
        $resultKey = 'idem:result:' . $idempotencyKey;

        $isLocked = (bool)$this->redis->setnx(
            key: $lockKey,
            value: '1',
        );
        if ($isLocked) {
            try {
                $cached = $this->redis->get($resultKey);
                if ($cached !== null) {
                    return json_decode($cached, true);
                }

                $result = $callback();

                $this->redis->setex(
                    $resultKey,
                    $this->resultTtl24hours,
                    json_encode($result),
                );

                return $result;
            } catch (Throwable $e) {
                $this->redis->setex(
                    $resultKey,
                    $this->resultTtl24hours,
                    json_encode($e->getMessage()),
                );

                throw $e;
            } finally {
                $this->redis->del($lockKey);
            }
        }

        $cached = $this->redis->get($resultKey);
        if ($cached !== null) {
            return json_decode($cached, true);
        }

        $waited = 0;
        while ($waited < $this->waitTimeout) {
            usleep(200000);

            $cached = $this->redis->get($resultKey);
            if ($cached !== null) {
                return json_decode($cached, true);
            }

            $waited += 0.2;
        }

        throw new Exception(
            'Request is still processing, please retry later',
            423,
        );
    }
}
