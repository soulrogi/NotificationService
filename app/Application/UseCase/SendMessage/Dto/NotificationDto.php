<?php

declare(strict_types=1);

namespace App\Application\UseCase\SendMessage\Dto;

use App\Domain\Entity\ValueObject\Channel;
use App\Domain\Entity\ValueObject\NotificationId;

readonly class NotificationDto
{
    public function __construct(
        private string $notificationId,
        private string $recipientId,
        private string $channel,
        private string $priority,
        private string $message,
    ) {
    }

    public function notificationId(): NotificationId
    {
        return NotificationId::create($this->notificationId);
    }

    public function channel(): Channel
    {
        return Channel::create($this->channel);
    }

    public static function fromJson(
        string $json,
    ): self {
        $data = json_decode(
            json: $json,
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        return new self(
            notificationId: $data['id'],
            recipientId: $data['recipient_id'],
            channel: $data['channel'],
            priority: $data['priority'],
            message: $data['message'],
        );
    }
}
