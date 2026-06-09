<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\UseCase\History\HistoryUseCase;
use App\Infrastructure\Http\Response\HistoryResponse;
use OpenApi\Attributes as OA;

readonly class HistoryAction
{
    public function __construct(
        private HistoryUseCase $useCase,
    ) {
    }

    #[OA\Get(
        path: '/api/history/recipient/{id}',
        description: 'Возвращает все уведомления для указанного получателя.',
        summary: 'Получить историю уведомлений получателя',
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Идентификатор получателя',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Массив уведомлений получателя',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Notification'),
                ),
            ),
        ],
    )]
    public function __invoke(
        string $id,
    ): HistoryResponse {
        return new HistoryResponse(
            data: $this->useCase->handle($id),
        );
    }
}
