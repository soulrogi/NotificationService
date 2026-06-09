<?php

declare(strict_types=1);

namespace App\Infrastructure\Kafka;

use RdKafka\Conf;

class Config
{
    public function __construct(
        private string $host,
        private bool $isDebug = false,
    ) {
    }

    public function getProducerConfig(): Conf
    {
        $conf = new Conf();

        $conf->set('metadata.broker.list', $this->host);
        $conf->set('bootstrap.servers', $this->host);

        $conf->set('socket.timeout.ms', '5000');
        $conf->set('queue.buffering.max.messages', '1000');
        $conf->set('queue.buffering.max.ms', '100');
        $conf->set('batch.num.messages', '1000');

        if ($this->isDebug) {
            $conf->set('log_level', (string)LOG_DEBUG);
            $conf->set('debug', 'all');
        }

        return $conf;
    }

    public function getConfigForConsumer(string $groupId): Conf
    {
        $conf = new Conf();

        $conf->set('metadata.broker.list', $this->host);
        $conf->set('bootstrap.servers', $this->host);

        $conf->set('group.id', $groupId);
        $conf->set('auto.offset.reset', 'earliest');
        $conf->set('enable.auto.commit', 'false');
        $conf->set('socket.timeout.ms', '5000');

        if ($this->isDebug) {
            $conf->set('log_level', (string)LOG_DEBUG);
            $conf->set('debug', 'all');
        }

        return $conf;
    }
}
