<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Notification;

interface Dispatcher
{
    /**
     * @param Notification[] $notifications
     */
    public function dispatch(
        array $notifications,
    ): void;
}
