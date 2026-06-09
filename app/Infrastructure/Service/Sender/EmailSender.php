<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Sender;

use App\Domain\Entity\Notification;
use App\Domain\Entity\ValueObject\Channel;
use App\Domain\Service\Sender\Sender;

class EmailSender implements Sender
{
    public function send(
        Notification $notification,
    ): string {
        return 'email_' . bin2hex(random_bytes(16));
    }

    public function channel(): Channel
    {
        return Channel::EMAIL;
    }

    public function checkDelivery(
        Notification $notification,
    ): void {
    }
}
