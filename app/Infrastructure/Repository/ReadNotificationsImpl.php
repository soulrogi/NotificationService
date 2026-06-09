<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Notification;
use App\Domain\Entity\ValueObject\Channel;
use App\Domain\Entity\ValueObject\Message;
use App\Domain\Entity\ValueObject\NotificationId;
use App\Domain\Entity\ValueObject\Priority;
use App\Domain\Entity\ValueObject\RecipientId;
use App\Domain\Entity\ValueObject\Status;
use App\Domain\Repository\ReadNotifications;
use DateTimeImmutable;
use DomainException;
use Illuminate\Database\Connection as ConnectionDB;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Psr\Log\LoggerInterface;
use Throwable;

class ReadNotificationsImpl implements ReadNotifications
{
    private ConnectionDB $connectionDB;

    public function __construct(
        private readonly LoggerInterface $logger,
        DatabaseManager $dbm,
    ) {
        $this->connectionDB = $dbm->connection();
    }

    /**
     * @inheritDoc
     */
    public function findById(
        NotificationId $id,
    ): Notification {
        try {
            /** @var array|null $row */
            $row = $this
                ->getBaseQuery()
                ->where('id', $id->value())
                ->first()
            ;

            if ($row === null) {
                throw new DomainException('Not found!');
            }

            return $this->hydrateNotification($row);
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
    public function findByRecipientId(
        RecipientId $recipientId,
    ): array {
        try {
            $rows = $this
                ->getBaseQuery()
                ->where('recipient_id', $recipientId->value())
                ->get()
                ->all()
            ;

            return array_map([$this, 'hydrateNotification'], $rows);
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
    public function findForRetries(
        int $limit = 100,
    ): array {
        try {
            $rows = $this
                ->getBaseQuery()
                ->where('status', Status::DISCARDED)
                ->orderBy('created_at')
                ->limit($limit)
                ->get()
                ->all()
            ;

            return array_map([$this, 'hydrateNotification'], $rows);
        } catch (Throwable $e) {
            $this->logger->error(__METHOD__, [
                'limit' => $limit,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    private function getBaseQuery(): Builder
    {
        return $this->connectionDB
            ->query()
            ->from('notifications')
        ;
    }

    private static function hydrateNotification(
        mixed $data,
    ): Notification {
        /** @var array<string, mixed> $data */
        $data = (array)$data;

        return Notification::create(
            id: NotificationId::create($data['id']),
            recipientId: RecipientId::create($data['recipient_id']),
            channel: Channel::create($data['channel']),
            priority: Priority::create($data['priority']),
            status: Status::create($data['status']),
            message: Message::create($data['message']),
            retryCount: (int)($data['retry_count'] ?? null),
            maxRetries: (int)($data['max_retries'] ?? null),
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: (empty($data['updated_at']) ? null : new DateTimeImmutable($data['updated_at'])),
            sentAt: (empty($data['sent_at']) ? null : new DateTimeImmutable($data['sent_at'])),
            externalId: (string)($data['external_id'] ?? null),
            errorMessage: (string)($data['error_message'] ?? null),
        );
    }
}
