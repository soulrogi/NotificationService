<?php

declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Application\UseCase\SendMessage\Dto\NotificationDto;
use App\Application\UseCase\SendMessage\SendMessageUseCase;
use App\Infrastructure\Kafka\Consumer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('notifications:consume {--topic=}')]
#[Description('Отправка уведомлений')]
class NotificationsCommand extends Command
{
    public function __construct(
        private Consumer $consumer,
        private SendMessageUseCase $useCase,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $topic = $this->option('topic');

        $consumer = $this->consumer->withGroupIdAndTopics(
            groupId: $topic . '-queue-consumer',
            topics: [$topic],
        );

        while (true) {
            try {
                $result = $consumer->consume(
                    callback: fn (string $json) => $this->useCase->handler(
                        dto: NotificationDto::fromJson($json),
                    ),
                );

                if ($result === null) {
                    $this->info('Новых сообщений нет.');

                    continue;
                }
            } catch (Throwable $e) {
                $this->info($e->getMessage());

                continue;
            }
        }
    }
}
