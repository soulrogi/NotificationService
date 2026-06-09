<?php

declare(strict_types=1);

namespace App\Domain\Entity\ValueObject;

use InvalidArgumentException;

final readonly class Message
{
    private function __construct(
        private string $value,
    ) {
        if (mb_strlen($value) > 10000) {
            throw new InvalidArgumentException('Message content exceeds maximum length of 10000 characters');
        }
    }

    public static function create(
        string $value,
    ): self {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
