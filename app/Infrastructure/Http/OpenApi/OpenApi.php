<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Notification Sending API',
    description: 'Микросервис отправки уведомлений.',
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: 'Local development server',
)]
#[OA\Tag(
    name: 'Notifications',
    description: 'Операции с уведомлениями',
)]
class OpenApi
{
}
