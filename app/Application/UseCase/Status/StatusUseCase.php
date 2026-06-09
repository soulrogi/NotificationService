<?php

declare(strict_types=1);

namespace App\Application\UseCase\Status;

use App\Application\UseCase\Status\Dto\StatusDto;
use App\Domain\Entity\ValueObject\NotificationId;
use App\Domain\Repository\ReadNotifications;

readonly class StatusUseCase
{
    public function __construct(
        private ReadNotifications $readNotifications,
    ) {
    }

    public function handle(
        string $uuid,
    ): StatusDto {
        return new StatusDto(
            notification: $this->readNotifications->findById(
                id: NotificationId::create($uuid),
            ),
        );
    }
}
