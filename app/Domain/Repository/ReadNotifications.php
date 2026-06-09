<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Notification;
use App\Domain\Entity\ValueObject\NotificationId;
use App\Domain\Entity\ValueObject\RecipientId;

interface ReadNotifications
{
    public function findById(
        NotificationId $id
    ): Notification;

    /**
     * @return Notification[]
     */
    public function findByRecipientId(
        RecipientId $recipientId,
    ): array;

    /**
     * @return Notification[]
     */
    public function findForRetries(
        int $limit = 100,
    ): array;
}
