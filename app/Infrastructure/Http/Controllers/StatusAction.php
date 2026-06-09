<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\UseCase\Status\StatusUseCase;
use App\Infrastructure\Http\Response\StatusResponse;
use OpenApi\Attributes as OA;

readonly class StatusAction
{
    public function __construct(
        private StatusUseCase $useCase,
    ) {
    }

    #[OA\Get(
        path: '/api/status/{uuid}',
        operationId: 'getNotificationStatus',
        description: 'Возвращает детальную информацию и текущий статус уведомления по его UUID.',
        summary: 'Получить статус уведомления',
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                description: 'UUID уведомления',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Детальная информация об уведомлении',
                content: new OA\JsonContent(ref: '#/components/schemas/Notification'),
            ),
            new OA\Response(
                response: 500,
                description: 'Уведомление не найдено или внутренняя ошибка',
            ),
        ],
    )]
    public function __invoke(
        string $uuid,
    ): StatusResponse {
        return new StatusResponse(
            data: $this->useCase->handle($uuid),
        );
    }
}
