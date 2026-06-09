<?php

declare(strict_types=1);

namespace App\Domain\Entity\ValueObject;

use InvalidArgumentException;

enum Channel: string
{
    case SMS = 'sms';
    case EMAIL = 'email';

    public static function create(
        string $value,
    ): self {
        return match ($value) {
            'sms' => self::SMS,
            'email' => self::EMAIL,

            default => throw new InvalidArgumentException("Invalid channel: $value"),
        };
    }

    public function equal(
        self $channel,
    ): bool {
        return ($this->value === $channel->value);
    }
}
