<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Notification',
    description: 'Данные уведомления',
)]
class Notification
{
    #[OA\Property(
        property: 'id',
        description: 'UUID уведомления',
        type: 'string',
        format: 'uuid',
    )]
    public string $id;

    #[OA\Property(
        property: 'recipient_id',
        description: 'Идентификатор получателя',
        type: 'string',
    )]
    public string $recipientId;

    #[OA\Property(
        property: 'channel',
        description: 'Канал отправки',
        type: 'string',
        enum: ['sms', 'email'],
    )]
    public string $channel;

    #[OA\Property(
        property: 'priority',
        description: 'Приоритет',
        type: 'string',
        enum: ['high', 'low']),
    ]
    public string $priority;

    #[OA\Property(
        property: 'status',
        description: 'Статус уведомления',
        type: 'string',
        enum: [
            'pending',
            'queued',
            'sent',
            'delivered',
            'discarded',
        ],
    )]
    public string $status;

    #[OA\Property(
        property: 'message',
        description: 'Текст сообщения',
        type: 'string',
    )]
    public string $message;

    #[OA\Property(
        property: 'external_id',
        description: 'Внешний ID о провайдера',
        type: 'string',
        nullable: true,
    )]
    public ?string $externalId;

    #[OA\Property(
        property: 'error_message',
        description: 'Сообщение об ошибке',
        type: 'string',
        nullable: true,
    )]
    public ?string $errorMessage;

    #[OA\Property(
        property: 'retry_count',
        description: 'Количество попыток отправки',
        type: 'integer',
    )]
    public int $retryCount;

    #[OA\Property(
        property: 'created_at',
        description: 'Дата создания',
        type: 'string',
        format: 'date-time',
    )]
    public string $createdAt;

    #[OA\Property(
        property: 'sent_at',
        description: 'Дата отправки',
        type: 'string',
        format: 'date-time',
        nullable: true,
    )]
    public ?string $sentAt;
}
