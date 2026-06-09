<?php

declare(strict_types=1);

namespace App\Infrastructure\Kafka;

use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;

class Producer
{
    private KafkaProducer $producer;

    /** @var ProducerTopic[] */
    private array $topics = [];

    public function __construct(
        Config $config,
    ) {
        $this->producer = new KafkaProducer($config->getProducerConfig());
    }

    public function produce(
        string $topicName,
        string $message,
        ?string $key = null,
        int $timeoutMs = 10000,
    ): void {
        $topic = $this->getTopic($topicName);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message, $key);
        $this->producer->poll(0);

        $this->flush($timeoutMs);
    }

    /**
     * @param string[] $messages
     */
    public function butchProduce(
        string $topicName,
        array $messages,
        ?string $key = null,
        int $timeoutMs = 10000,
    ): void {
        if (empty($messages)) {
            return;
        }

        foreach ($messages as $message) {
            $topic = $this->getTopic($topicName);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message, $key);
            $this->producer->poll(0);
        }

        $this->flush($timeoutMs);
    }

    private function flush(int $timeoutMs = 10000): void
    {
        $this->producer->flush($timeoutMs);
    }

    private function getTopic(string $topicName): ProducerTopic
    {
        if (!array_key_exists($topicName, $this->topics)) {
            $this->topics[$topicName] = $this->producer->newTopic($topicName);
        }

        return $this->topics[$topicName];
    }
}
