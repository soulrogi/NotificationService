<?php

declare(strict_types=1);

namespace App\Domain\Service\Sender;

use App\Domain\Entity\Notification;
use App\Domain\Entity\ValueObject\Channel;

interface Sender
{
    public function send(
        Notification $notification,
    ): string;
    public function channel(): Channel;
    public function checkDelivery(
        Notification $notification,
    ): void;
}
