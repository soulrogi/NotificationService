<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Notification;
use App\Domain\Entity\ValueObject\NotificationId;
use App\Domain\Entity\ValueObject\Status;
use App\Domain\Repository\WriteNotifications;
use Illuminate\Database\Connection as ConnectionDB;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Throwable;

class WriteNotificationsImpl implements WriteNotifications
{
    private ConnectionDB $connectionDB;

    public function __construct(
        private LoggerInterface $logger,
        DatabaseManager $dbm,
    ) {
        $this->connectionDB = $dbm->connection();
    }

    public function save(
        Notification $notification,
    ): void {
        $this->batchSave([$notification]);
    }

    /**
     * @param Notification[] $notifications
     */
    public function batchSave(
        array $notifications,
    ): void {
        try {
            $values = array_map(
                static fn(Notification $notification): array => $notification->toArray(),
                $notifications,
            );

            $this->connectionDB
                ->query()
                ->from('notifications')
                ->upsert($values, ['id'])
            ;
        } catch (Throwable $e) {
            $this->logger->error(__METHOD__, [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function changeStatusToByIds(
        Status $status,
        array $notificationIds,
    ): void {
        if (empty($notificationIds)) {
            return;
        }

        try {
            $this->connectionDB
                ->query()
                ->from('notifications')
                ->whereIn('id', $notificationIds)
                ->update(['status' => $status]);
        } catch (Throwable $e) {
            $this->logger->error(__METHOD__, [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function changeStatusToById(
        Status $status,
        NotificationId $notificationId,
    ): void {
        $this->changeStatusToByIds(
            status: $status,
            notificationIds: [$notificationId],
        );
    }
}
