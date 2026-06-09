<?php

declare(strict_types=1);

namespace App\Infrastructure\Kafka;

use Exception;
use RdKafka\KafkaConsumer;
use RuntimeException;
use Throwable;

class Consumer
{
    private ?KafkaConsumer $consumer = null;

    public function __construct(
        private Config $config,
    ) {
    }

    /**
     * @param string[] $topics
     */
    public function withGroupIdAndTopics(
        string $groupId,
        array $topics,
    ): self {
        $clone = clone $this;

        $clone->consumer = new KafkaConsumer(
            conf: $this->config->getConfigForConsumer($groupId),
        );

        $clone->consumer()->subscribe($topics);

        return $clone;
    }

    public function consume(
        callable $callback,
        int $timeoutMs = 100000,
    ): mixed {
        $message = $this->consumer()->consume($timeoutMs);

        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                try {
                    $result = $callback($message->payload);

                    $this->consumer()->commit();

                    return $result;
                } catch (Throwable $e) {
//                  todo перекинуть в очередь с ошибками
                    throw $e;
                }

            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
//              todo Новых сообщений нет
                return null;

            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                throw new Exception('Timed out');

            default:
                throw new Exception($message->errstr(), $message->err);
        }
    }

    public function close(): void
    {
        $this->consumer()->close();
    }

    public function __clone(): void
    {
        $this->config = clone $this->config;
    }

    private function consumer(): KafkaConsumer
    {
        return ($this->consumer ?? throw new RuntimeException('Consumer is not initialized'));
    }
}
