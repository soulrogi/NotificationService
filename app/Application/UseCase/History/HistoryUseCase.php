<?php

declare(strict_types=1);

namespace App\Application\UseCase\History;

use App\Application\UseCase\History\Dto\HistoryDto;
use App\Domain\Entity\ValueObject\RecipientId;
use App\Domain\Repository\ReadNotifications;

readonly class HistoryUseCase
{
    public function __construct(
        private ReadNotifications $readNotifications,
    ) {
    }

    /**
     * @return HistoryDto[]
     */
    public function handle(
        string $id,
    ): array {
        return array_map(
            static fn($notification) => new HistoryDto($notification),
            $this->readNotifications->findByRecipientId(
                recipientId: RecipientId::create($id),
            ),
        );
    }
}
