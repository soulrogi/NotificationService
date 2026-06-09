<?php

declare(strict_types=1);

namespace App\Domain\Entity\ValueObject;

use InvalidArgumentException;

final class RecipientId
{
    private string $value;

    public function __construct(
        string $value,
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('Recipient ID cannot be empty');
        }
        $this->value = $value;
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

    public function equals(
        RecipientId $id
    ): bool {
        return $this->value() === $id->value();
    }
}
