<?php

declare(strict_types=1);

namespace App\Application\UseCase\History\Dto;

use App\Domain\Entity\Notification;
use JsonSerializable;

readonly class HistoryDto implements JsonSerializable
{
    public function __construct(
        private Notification $notification,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->notification->id()->value(),
            'recipient_id' => $this->notification->recipientId()->value(),
            'channel' => $this->notification->channel()->value,
            'priority' => $this->notification->priority()->value,
            'status' => $this->notification->status()->value,
            'message' => $this->notification->message()->value(),
            'external_id' => $this->notification->externalId(),
            'error_message' => $this->notification->errorMessage(),
            'retry_count' => $this->notification->retryCount(),
            'created_at' => $this->notification->createdAt()->format('Y-m-d H:i:s.u'),
            'sent_at' => $this->notification->sentAt()?->format('Y-m-d H:i:s.u'),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
