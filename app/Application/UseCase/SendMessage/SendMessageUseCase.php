<?php

declare(strict_types=1);

namespace App\Application\UseCase\SendMessage;

use App\Application\Service\Synchronization\MutexFactory;
use App\Application\UseCase\SendMessage\Dto\NotificationDto;
use App\Domain\Entity\Notification;
use App\Domain\Repository\ReadNotifications;
use App\Domain\Repository\WriteNotifications;
use App\Domain\Service\Dispatcher;
use App\Domain\Service\Sender\SenderProvider;
use function Symfony\Component\String\s;

readonly class SendMessageUseCase
{
    public function __construct(
        private WriteNotifications $writeNotifications,
        private ReadNotifications $readNotifications,
        private SenderProvider $senderProvider,
        private Dispatcher $dispatcher,
        private MutexFactory $mutexFactory
    ) {
    }

    public function handler(
        NotificationDto $dto,
    ): Notification {
        $mutex = $this->mutexFactory->create(
            name: $dto->notificationId()->value(),
            timeout: 10,
        );

        $mutex->lock();

        $notification = $this->readNotifications->findById($dto->notificationId());

        try {
            $notification->send($this->senderProvider);
            $this->writeNotifications->save($notification);

            $notification->checkDelivery($this->senderProvider);
            $this->writeNotifications->save($notification);

            if ($notification->canRetry()) {
                $notification->incrementRetry();

                $this->writeNotifications->save($notification);

                $this->dispatcher->dispatch([$notification]);
            }
        } finally {
            $mutex->unlock();
        }

        return $notification;
    }
}
