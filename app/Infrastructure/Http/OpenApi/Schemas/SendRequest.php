<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SendRequest',
    description: 'Запрос на отправку уведомления',
)]
class SendRequest
{
    #[OA\Property(
        property: 'recipient_ids',
        description: 'Массив ID получателей',
        type: 'array',
        items: new OA\Items(type: 'string', minLength: 1),
        minItems: 1,
        maxItems: 1000,
    )]
    public array $recipientIds;

    #[OA\Property(
        property: 'channel',
        description: 'Канал отправки',
        type: 'string',
        enum: ['sms', 'email'],
    )]
    public string $channel;

    #[OA\Property(
        property: 'message',
        description: 'Текст сообщения',
        type: 'string',
        minLength: 1,
        maxLength: 10000,
    )]
    public string $message;

    #[OA\Property(
        property: 'priority',
        description: 'Приоритет уведомления',
        type: 'string',
        enum: ['high', 'low'],
    )]
    public string $priority;

    #[OA\Property(
        property: 'idempotency_key',
        description: 'Ключ идемпотентности',
        type: 'string',
        nullable: true,
    )]
    public ?string $idempotencyKey;
}
