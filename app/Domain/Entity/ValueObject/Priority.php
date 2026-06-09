<?php

declare(strict_types=1);

namespace App\Domain\Entity\ValueObject;

use InvalidArgumentException;

enum Priority: string
{
    case HIGH = 'high';
    case LOW = 'low';

    public static function create(
        string $value,
    ): self {
        return match ($value) {
            'high' => self::HIGH,
            'low' => self::LOW,

            default => throw new InvalidArgumentException('Invalid priority: ' . $value),
        };
    }

    public function isHigh(): bool
    {
        return ($this->value === self::HIGH->value);
    }

    public function isLow(): bool
    {
        return ($this->value === self::LOW->value);
    }
}
