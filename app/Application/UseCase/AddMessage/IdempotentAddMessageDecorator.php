<?php

declare(strict_types=1);

namespace App\Application\UseCase\AddMessage;

use App\Application\Service\IdempotentWrapper;
use App\Application\UseCase\AddMessage\Dto\AddMessageDto;

readonly class IdempotentAddMessageDecorator implements AddMessageUseCase
{
    public function __construct(
        private AddMessageUseCase $useCase,
        private IdempotentWrapper $idempotentWrapper,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(
        AddMessageDto $dto,
    ): array {
        if ($dto->hasIdempotencyKey()) {
            return (array)$this->idempotentWrapper->wrap(
                idempotencyKey: $dto->idempotencyKey,
                callback: fn () => $this->useCase->handle($dto),
            );
        }

        return $this->useCase->handle($dto);
    }
}
