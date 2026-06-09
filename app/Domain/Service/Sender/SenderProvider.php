<?php

declare(strict_types=1);

namespace App\Domain\Service\Sender;

use App\Domain\Entity\ValueObject\Channel;
use Exception;

class SenderProvider
{
    /**
     * @param Sender[] $senders
     */
    public function __construct(
        private array $senders,
    ) {
    }
    public function getBy(
        Channel $channel,
    ): Sender {
        foreach ($this->senders as $sender) {
            if ($channel->equal($sender->channel())) {
                return $sender;
            }
        }

        throw new Exception('Sender not found');
    }
}
