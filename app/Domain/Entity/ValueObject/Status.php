<?php

declare(strict_types=1);

namespace App\Domain\Entity\ValueObject;

use InvalidArgumentException;

enum Status: string
{
    case PENDING = 'pending';
    case QUEUED = 'queued';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case DISCARDED = 'discarded';

    public static function create(
        string $value,
    ): self {
        return match ($value) {
            'pending' => self::PENDING,
            'queued' => self::QUEUED,
            'sent' => self::SENT,
            'delivered' => self::DELIVERED,
            'discarded' => self::DISCARDED,

            default => throw new InvalidArgumentException('Invalid status: ' . $value),
        };
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING->value;
    }

    public function isQueued(): bool
    {
        return $this->value === self::QUEUED->value;
    }

    public function isSent(): bool
    {
        return $this->value === self::SENT->value;
    }

    public function isDelivered(): bool
    {
        return $this->value === self::DELIVERED->value;
    }

    public function isDiscarded(): bool
    {
        return $this->value === self::DISCARDED->value;
    }

    public function isNotPending(): bool
    {
        return (!$this->isPending());
    }

    public function isNotQueued(): bool
    {
        return (!$this->isQueued());
    }

    public function isNotSent(): bool
    {
        return (!$this->isSent());
    }

    public function isNotDelivered(): bool
    {
        return (!$this->isDelivered());
    }

    public function isNotDiscarded(): bool
    {
        return (!$this->isDiscarded());
    }
}
