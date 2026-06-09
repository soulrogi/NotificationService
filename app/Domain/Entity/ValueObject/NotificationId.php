<?php

declare(strict_types=1);

namespace App\Domain\Entity\ValueObject;

use Ramsey\Uuid\Uuid;
use Stringable;

final class NotificationId implements Stringable
{
    private string $value;

    public function __construct(
        ?string $value = null,
    ) {
        $this->value = $value ?? Uuid::uuid7()->toString();
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
        NotificationId $id,
    ): bool {
        return $this->value === $id->value;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
