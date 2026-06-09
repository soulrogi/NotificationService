<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Service\Dispatcher;
use App\Infrastructure\Kafka\Producer;

class DispatcherImpl implements Dispatcher
{
    public function __construct(
        private Producer $producer,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function dispatch(
        array $notifications,
    ): void {
        $lowPriority = [];
        $highPriority = [];
        foreach ($notifications as $notification) {
            if($notification->priority()->isLow()) {
                $lowPriority[] = $notification->toJson();

                continue;
            }

            if($notification->priority()->isHigh()) {
                $highPriority[] = $notification->toJson();

                continue;
            }
        }

        $this->producer->butchProduce(
            topicName: 'low',
            messages: $lowPriority,
        );

        $this->producer->butchProduce(
            topicName: 'high',
            messages: $highPriority,
        );
    }
}
