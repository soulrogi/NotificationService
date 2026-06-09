<?php

declare(strict_types=1);

namespace App\Application\UseCase\AddMessage;

use App\Application\UseCase\AddMessage\Dto\AddMessageDto;

interface AddMessageUseCase
{
    /**
     * @return string[]
     */
    public function handle(
        AddMessageDto $dto,
    ): array;
}
