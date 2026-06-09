<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\UseCase\AddMessage\AddMessageUseCase;
use App\Infrastructure\Http\Request\SendRequest;
use App\Infrastructure\Http\Response\SendResponse;
use OpenApi\Attributes as OA;

readonly class AddMessageAction
{
    public function __construct(
        private AddMessageUseCase $useCase,
    ) {
    }

    #[OA\Post(
        path: '/api/add',
        operationId: 'addNotification',
        description: 'Создаёт одно или несколько уведомлений для указанных получателей и отправляет их в очередь Kafka.',
        summary: 'Создать уведомление',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SendRequest'),
        ),
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешное создание. Возвращает массив UUID созданных уведомлений.',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(type: 'string', format: 'uuid'),
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function __invoke(
        SendRequest $request,
    ): SendResponse {
        return new SendResponse(
            data: $this->useCase->handle(
                dto: $request->getDto(),
            ),
        );
    }
}
