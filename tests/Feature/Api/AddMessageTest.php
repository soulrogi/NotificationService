<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Application\Service\IdempotentWrapper;
use App\Domain\Entity\ValueObject\Status;
use App\Domain\Service\Dispatcher;
use App\Infrastructure\Service\IdempotentWrapperImpl;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\Doubles\FakeIdempotentWrapper;
use Tests\TestCase;

class AddMessageTest extends TestCase
{
    use DatabaseMigrations;

    /** @var mixed[] */
    private array $dispatchedNotifications = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatchedNotifications = [];

        $this->app->singleton(Dispatcher::class, function () {
            $mock = $this->createMock(Dispatcher::class);
            $mock->method('dispatch')->willReturnCallback(
                function (array $notifications): void {
                    $this->dispatchedNotifications = $notifications;
                },
            );
            return $mock;
        });

        $this->app->singleton(IdempotentWrapperImpl::class, function () {
            return new FakeIdempotentWrapper();
        });

        $this->app->singleton(IdempotentWrapper::class, function () {
            return new FakeIdempotentWrapper();
        });
    }

    #[Test]
    public function it_creates_notifications_for_multiple_recipients(): void
    {
        $response = $this->postJson('/api/add', [
            'recipient_ids' => ['user-1', 'user-2', 'user-3'],
            'channel' => 'sms',
            'message' => 'Hello, this is a test message',
            'priority' => 'high',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $ids = $response->json();
        $this->assertCount(3, $ids);

        foreach ($ids as $id) {
            $this->assertTrue(Uuid::isValid($id), "Expected valid UUID, got: $id");
        }

        $this->assertDatabaseCount('notifications', 3);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('notifications', [
                'id' => $id,
                'status' => 'queued',
                'channel' => 'sms',
                'priority' => 'high',
                'retry_count' => 0,
                'max_retries' => 3,
            ]);
        }
    }

    #[Test]
    public function it_uses_default_channel_and_priority(): void
    {
        $response = $this->postJson('/api/add', [
            'recipient_ids' => ['user-1'],
            'message' => 'Default values test',
        ]);

        $response->assertStatus(200);

        $ids = $response->json();
        $this->assertCount(1, $ids);

        $this->assertDatabaseHas('notifications', [
            'id' => $ids[0],
            'channel' => 'sms',
            'priority' => 'low',
            'message' => 'Default values test',
        ]);
    }

    #[Test]
    public function it_creates_email_notification(): void
    {
        $response = $this->postJson('/api/add', [
            'recipient_ids' => ['email@example.com'],
            'channel' => 'email',
            'message' => 'Email notification',
            'priority' => 'high',
        ]);

        $response->assertStatus(200);

        $ids = $response->json();
        $this->assertDatabaseHas('notifications', [
            'id' => $ids[0],
            'channel' => 'email',
            'priority' => 'high',
        ]);
    }

    #[Test]
    public function it_returns_same_result_with_idempotency_key(): void
    {
        $payload = [
            'recipient_ids' => ['user-1'],
            'channel' => 'sms',
            'message' => 'Idempotent request',
            'priority' => 'low',
            'idempotency_key' => 'unique-key-123',
        ];

        $firstResponse = $this->postJson('/api/add', $payload);
        $firstResponse->assertStatus(200);

        $secondResponse = $this->postJson('/api/add', $payload);
        $secondResponse->assertStatus(200);

        $this->assertSame($firstResponse->json(), $secondResponse->json());
    }

    #[Test]
    public function it_fails_when_recipient_ids_missing(): void
    {
        $response = $this->postJson('/api/add', [
            'message' => 'Missing recipients',
            'channel' => 'sms',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('recipient_ids');
    }

    #[Test]
    public function it_fails_when_recipient_ids_is_empty(): void
    {
        $response = $this->postJson('/api/add', [
            'recipient_ids' => [],
            'message' => 'Empty recipients',
            'channel' => 'sms',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('recipient_ids');
    }

    #[Test]
    public function it_fails_when_message_missing(): void
    {
        $response = $this->postJson('/api/add', [
            'recipient_ids' => ['user-1'],
            'channel' => 'sms',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('message');
    }

    #[Test]
    public function it_fails_with_invalid_channel(): void
    {
        $response = $this->postJson('/api/add', [
            'recipient_ids' => ['user-1'],
            'channel' => 'telegram',
            'message' => 'Invalid channel',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('channel');
    }

    #[Test]
    public function it_fails_with_invalid_priority(): void
    {
        $response = $this->postJson('/api/add', [
            'recipient_ids' => ['user-1'],
            'channel' => 'sms',
            'message' => 'Invalid priority',
            'priority' => 'urgent',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('priority');
    }

    #[Test]
    public function it_fails_when_message_exceeds_max_length(): void
    {
        $response = $this->postJson('/api/add', [
            'recipient_ids' => ['user-1'],
            'channel' => 'sms',
            'message' => str_repeat('A', 10001),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('message');
    }

    #[Test]
    public function it_fails_when_recipient_ids_exceeds_max_count(): void
    {
        $response = $this->postJson('/api/add', [
            'recipient_ids' => range(1, 1001),
            'channel' => 'sms',
            'message' => 'Too many recipients',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('recipient_ids');
    }

    #[Test]
    public function it_dispatches_notifications_to_queue(): void
    {
        $this->postJson('/api/add', [
            'recipient_ids' => ['user-1', 'user-2'],
            'channel' => 'email',
            'message' => 'Dispatch test',
            'priority' => 'high',
        ]);

        $this->assertCount(2, $this->dispatchedNotifications);

        foreach ($this->dispatchedNotifications as $notification) {
            $this->assertSame('high', $notification->priority()->value);
            $this->assertSame('email', $notification->channel()->value);
            $this->assertSame(Status::QUEUED, $notification->status());
        }
    }
}
