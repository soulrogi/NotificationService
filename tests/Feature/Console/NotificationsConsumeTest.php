<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Application\Service\Synchronization\MutexFactory;
use App\Application\UseCase\SendMessage\Dto\NotificationDto;
use App\Application\UseCase\SendMessage\SendMessageUseCase;
use App\Domain\Entity\ValueObject\Status;
use App\Domain\Service\Dispatcher;
use App\Infrastructure\Console\Commands\NotificationsCommand;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use Tests\Doubles\FakeMutexFactory;
use Tests\TestCase;

class NotificationsConsumeTest extends TestCase
{
    use DatabaseMigrations;

    private SendMessageUseCase $useCase;

    /** @var mixed[] */
    private array $dispatchedNotifications = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatchedNotifications = [];

        $this->app->singleton(Dispatcher::class, function () {
            $mock = $this->createMock(Dispatcher::class);

            $mock
                ->method('dispatch')
                ->willReturnCallback(function (array $notifications): void {
                    $this->dispatchedNotifications = $notifications;
                })
            ;

            return $mock;
        });

        $this->app->singleton(
            MutexFactory::class,
            fn () => new FakeMutexFactory(),
        );

        $this->useCase = $this->app->make(SendMessageUseCase::class);
    }

    #[Test]
    public function it_processes_sms_notification_from_queued_to_delivered(): void
    {
        $id = Uuid::uuid7()->toString();

        DB::table('notifications')->insert([
            'id' => $id,
            'recipient_id' => 'user-sms',
            'channel' => 'sms',
            'priority' => 'high',
            'status' => 'queued',
            'message' => 'SMS test message',
            'retry_count' => 0,
            'max_retries' => 3,
            'created_at' => now()->format('Y-m-d H:i:s.u'),
        ]);

        $dto = NotificationDto::fromJson(json_encode([
            'id' => $id,
            'recipient_id' => 'user-sms',
            'channel' => 'sms',
            'priority' => 'high',
            'message' => 'SMS test message',
        ]));

        $notification = $this->useCase->handler($dto);

        $this->assertSame(Status::DELIVERED, $notification->status());

        $this->assertDatabaseHas('notifications', [
            'id' => $id,
            'status' => 'delivered',
            'channel' => 'sms',
        ]);

        $this->assertNotNull($notification->externalId());
        $this->assertStringStartsWith('sms_', $notification->externalId());
    }

    #[Test]
    public function it_processes_email_notification_from_queued_to_delivered(): void
    {
        $id = Uuid::uuid7()->toString();

        DB::table('notifications')->insert([
            'id' => $id,
            'recipient_id' => 'user-email',
            'channel' => 'email',
            'priority' => 'low',
            'status' => 'queued',
            'message' => 'Email test message',
            'retry_count' => 0,
            'max_retries' => 3,
            'created_at' => now()->format('Y-m-d H:i:s.u'),
        ]);

        $dto = NotificationDto::fromJson(json_encode([
            'id' => $id,
            'recipient_id' => 'user-email',
            'channel' => 'email',
            'priority' => 'low',
            'message' => 'Email test message',
        ]));

        $notification = $this->useCase->handler($dto);

        $this->assertSame(Status::DELIVERED, $notification->status());

        $this->assertDatabaseHas('notifications', [
            'id' => $id,
            'status' => 'delivered',
            'channel' => 'email',
        ]);

        $this->assertNotNull($notification->externalId());
        $this->assertStringStartsWith('email_', $notification->externalId());
    }

    #[Test]
    public function it_sets_external_id_on_successful_send(): void
    {
        $id = Uuid::uuid7()->toString();

        DB::table('notifications')->insert([
            'id' => $id,
            'recipient_id' => 'user-ext',
            'channel' => 'sms',
            'priority' => 'high',
            'status' => 'queued',
            'message' => 'External ID test',
            'retry_count' => 0,
            'max_retries' => 3,
            'created_at' => now()->format('Y-m-d H:i:s.u'),
        ]);

        $dto = NotificationDto::fromJson(json_encode([
            'id' => $id,
            'recipient_id' => 'user-ext',
            'channel' => 'sms',
            'priority' => 'high',
            'message' => 'External ID test',
        ]));

        $notification = $this->useCase->handler($dto);

        $this->assertNotNull($notification->externalId());
        $this->assertNotEmpty($notification->externalId());

        $this->assertDatabaseHas('notifications', [
            'id' => $id,
            'external_id' => $notification->externalId(),
        ]);
    }

    #[Test]
    public function it_sets_sent_at_timestamp_on_successful_send(): void
    {
        $id = Uuid::uuid7()->toString();

        DB::table('notifications')->insert([
            'id' => $id,
            'recipient_id' => 'user-time',
            'channel' => 'sms',
            'priority' => 'low',
            'status' => 'queued',
            'message' => 'Timestamp test',
            'retry_count' => 0,
            'max_retries' => 3,
            'created_at' => now()->format('Y-m-d H:i:s.u'),
        ]);

        $dto = NotificationDto::fromJson(json_encode([
            'id' => $id,
            'recipient_id' => 'user-time',
            'channel' => 'sms',
            'priority' => 'low',
            'message' => 'Timestamp test',
        ]));

        $notification = $this->useCase->handler($dto);

        $this->assertNotNull($notification->sentAt());
        $this->assertDatabaseMissing('notifications', [
            'id' => $id,
            'sent_at' => null,
        ]);
    }

    #[Test]
    public function it_creates_notification_dto_from_json(): void
    {
        $json = json_encode([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'recipient_id' => 'user-test',
            'channel' => 'email',
            'priority' => 'high',
            'message' => 'DTO test',
        ]);

        $dto = NotificationDto::fromJson($json);

        $this->assertSame(
            '550e8400-e29b-41d4-a716-446655440000',
            $dto->notificationId()->value(),
        );
    }

    #[Test]
    public function command_is_registered_with_correct_signature(): void
    {
        $reflection = new ReflectionClass(NotificationsCommand::class);
        $attributes = $reflection->getAttributes(Signature::class);

        $this->assertCount(1, $attributes);
        $this->assertStringContainsString(
            'notifications:consume',
            $attributes[0]->newInstance()->signature,
        );
    }

    #[Test]
    public function command_has_description(): void
    {
        $reflection = new ReflectionClass(NotificationsCommand::class);
        $attributes = $reflection->getAttributes(Description::class);

        $this->assertCount(1, $attributes);
        $this->assertNotEmpty($attributes[0]->newInstance()->description);
    }
}
