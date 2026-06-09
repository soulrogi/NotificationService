<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\ValueObject\Channel;
use App\Domain\Entity\ValueObject\Message;
use App\Domain\Entity\ValueObject\NotificationId;
use App\Domain\Entity\ValueObject\Priority;
use App\Domain\Entity\ValueObject\RecipientId;
use App\Domain\Entity\ValueObject\Status;
use App\Domain\Service\Sender\SenderProvider;
use DateTimeImmutable;
use Exception;

final class Notification
{
    private function __construct(
        private NotificationId $id,
        private RecipientId $recipientId,
        private Channel $channel,
        private Priority $priority,
        private Status $status,
        private Message $message,
        private int $retryCount,
        private int $maxRetries,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $sentAt = null,
        private ?string $errorMessage = null,
        private ?string $externalId = null,
    ) {
    }

    public static function createQueued(
        RecipientId $recipientId,
        Channel $channel,
        Priority $priority,
        Message $message,
        int $maxRetries = 3,
    ): self {
        return self::create(
            id: new NotificationId(),
            recipientId: $recipientId,
            channel: $channel,
            priority: $priority,
            status: Status::QUEUED,
            message: $message,
            retryCount: 0,
            maxRetries: $maxRetries,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function create(
        NotificationId $id,
        RecipientId $recipientId,
        Channel $channel,
        Priority $priority,
        Status $status,
        Message $message,
        int $retryCount,
        int $maxRetries,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt = null,
        ?DateTimeImmutable $sentAt = null,
        ?string $externalId = null,
        ?string $errorMessage = null,
    ): self {
        return new self(
            id: $id,
            recipientId: $recipientId,
            channel: $channel,
            priority: $priority,
            status: $status,
            message: $message,
            retryCount: $retryCount,
            maxRetries: $maxRetries,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            sentAt: $sentAt,
            errorMessage: $errorMessage,
            externalId: $externalId,
        );
    }

    public function markAsSent(
        string $externalId,
    ): void {
        $this->status = Status::SENT;
        $this->externalId = $externalId;
        $this->sentAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsQueued(
    ): void {
        $this->status = Status::QUEUED;
        $this->externalId = null;
        $this->sentAt = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsDelivered(): void
    {
        $this->status = Status::DELIVERED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsDiscarded(
        string $errorMessage,
    ): void {
        $this->status = Status::DISCARDED;
        $this->errorMessage = $errorMessage;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function canRetry(): bool
    {
        return (
            $this->retryCount() < $this->maxRetries() &&
            (
                $this->status->isNotDelivered() ||
                $this->status->isDiscarded()
            )
        );
    }

    public function incrementRetry(): void
    {
        $this->retryCount++;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): NotificationId
    {
        return $this->id;
    }

    public function recipientId(): RecipientId
    {
        return $this->recipientId;
    }

    public function channel(): Channel
    {
        return $this->channel;
    }

    public function priority(): Priority
    {
        return $this->priority;
    }

    public function status(): Status
    {
        return $this->status;
    }

    public function isStatusDiscarded(): bool
    {
        return $this->status()->isDiscarded();
    }

    public function message(): Message
    {
        return $this->message;
    }

    public function externalId(): ?string
    {
        return $this->externalId;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function retryCount(): int
    {
        return $this->retryCount;
    }

    public function maxRetries(): int
    {
        return $this->maxRetries;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function sentAt(): ?DateTimeImmutable
    {
        return $this->sentAt;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id()->value(),
            'recipient_id' => $this->recipientId()->value(),
            'channel' => $this->channel()->value,
            'priority' => $this->priority()->value,
            'status' => $this->status()->value,
            'message' => $this->message()->value(),
            'external_id' => $this->externalId(),
            'error_message' => $this->errorMessage(),
            'retry_count' => $this->retryCount(),
            'max_retries' => $this->maxRetries(),
            'created_at' => $this->createdAt()->format('Y-m-d H:i:s.u'),
            'updated_at' => $this->updatedAt()?->format('Y-m-d H:i:s.u'),
            'sent_at' => $this->sentAt()?->format('Y-m-d H:i:s.u'),
        ];
    }

    public function toJson(): string
    {
        return json_encode([
            'id' => $this->id()->value(),
            'recipient_id' => $this->recipientId()->value(),
            'channel' => $this->channel()->value,
            'priority' => $this->priority()->value,
            'status' => $this->status()->value,
            'message' => $this->message()->value(),
        ]);
    }

    public function send(
        SenderProvider $provider,
    ): void {
        if ($this->status()->isQueued()) {
            try {
                $externalId = $provider
                    ->getBy($this->channel())
                    ->send($this)
                ;

                $this->markAsSent(externalId: $externalId);
            } catch (Exception $e) {
                $this->markAsDiscarded(
                    errorMessage: $e->getMessage(),
                );
            }
        }
    }

    public function checkDelivery(
        SenderProvider $provider,
    ): void {
        if ($this->status()->isSent()) {
            try {
                $provider
                    ->getBy($this->channel())
                    ->checkDelivery($this)
                ;

                $this->markAsDelivered();
            } catch (Exception $e) {
                $this->markAsDiscarded(
                    errorMessage: $e->getMessage(),
                );
            }
        }
    }
}
