<?php

declare(strict_types=1);

namespace App\Application\UseCase\AddMessage;

use App\Application\UseCase\AddMessage\Dto\AddMessageDto;
use App\Domain\Entity\Notification;
use App\Domain\Entity\ValueObject\Channel;
use App\Domain\Entity\ValueObject\Message;
use App\Domain\Entity\ValueObject\Priority;
use App\Domain\Entity\ValueObject\RecipientId;
use App\Domain\Repository\WriteNotifications;
use App\Domain\Service\Dispatcher;

readonly class AddMessage implements AddMessageUseCase
{
    public function __construct(
        private WriteNotifications $writeNotifications,
        private Dispatcher $dispacher,
    ) {
    }

    /**
     * @return string[]
     */
    public function handle(
        AddMessageDto $dto,
    ): array {
        $notifications = array_map(
            static fn(string $recipientId) => Notification::createQueued(
                recipientId: RecipientId::create($recipientId),
                channel: Channel::create($dto->channel),
                priority: Priority::create($dto->priority),
                message: Message::create($dto->message),
            ),
            $dto->recipientIds,
        );

        $this->writeNotifications->batchSave($notifications);

        $this->dispacher->dispatch($notifications);

        return array_map(
            static fn(Notification $notification) => $notification->id()->value(),
            $notifications,
        );
    }
}
