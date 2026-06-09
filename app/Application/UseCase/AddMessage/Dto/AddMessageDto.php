<?php

declare(strict_types=1);

namespace App\Application\UseCase\AddMessage\Dto;

readonly class AddMessageDto
{
    /**
     * @param string[] $recipientIds
     */
    public function __construct(
        public array $recipientIds,
        public string $channel,
        public string $message,
        public string $priority,
        public ?string $idempotencyKey = null,
    ) {
    }

    public function hasIdempotencyKey(): bool
    {
        return ($this->idempotencyKey !== null);
    }
}
