<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Notification;
use App\Domain\Entity\ValueObject\NotificationId;
use App\Domain\Entity\ValueObject\Status;

interface WriteNotifications
{
    public function save(
        Notification $notification,
    ): void;

    /**
     * @param Notification[] $notifications
     */
    public function batchSave(
        array $notifications,
    ): void;

    /**
     * @param NotificationId[] $notificationIds
     */
    public function changeStatusToByIds(
        Status $status,
        array $notificationIds,
    ): void;

    public function changeStatusToById(
        Status $status,
        NotificationId $notificationId,
    ): void;
}
