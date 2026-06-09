<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_returns_notification_status_when_found(): void
    {
        $id = Uuid::uuid7()->toString();

        DB::table('notifications')->insert([
            'id' => $id,
            'recipient_id' => 'user-123',
            'channel' => 'sms',
            'priority' => 'high',
            'status' => 'sent',
            'message' => 'Status check message',
            'external_id' => 'ext_abc123',
            'error_message' => null,
            'retry_count' => 1,
            'max_retries' => 3,
            'created_at' => '2026-06-10 12:00:00.000000',
            'updated_at' => '2026-06-10 12:01:00.000000',
            'sent_at' => '2026-06-10 12:00:30.000000',
        ]);

        $response = $this->getJson("/api/status/{$id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $id,
            'recipient_id' => 'user-123',
            'channel' => 'sms',
            'priority' => 'high',
            'status' => 'sent',
            'message' => 'Status check message',
            'external_id' => 'ext_abc123',
            'error_message' => null,
            'retry_count' => 1,
            'created_at' => '2026-06-10 12:00:00.000000',
            'sent_at' => '2026-06-10 12:00:30.000000',
        ]);
    }

    #[Test]
    public function it_returns_queued_status_for_new_notification(): void
    {
        $id = Uuid::uuid7()->toString();

        DB::table('notifications')->insert([
            'id' => $id,
            'recipient_id' => 'user-456',
            'channel' => 'email',
            'priority' => 'low',
            'status' => 'queued',
            'message' => 'Queued message',
            'retry_count' => 0,
            'max_retries' => 3,
            'created_at' => now()->format('Y-m-d H:i:s.u'),
        ]);

        $response = $this->getJson("/api/status/{$id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $id,
            'status' => 'queued',
        ]);
    }

    #[Test]
    public function it_returns_discarded_status_with_error(): void
    {
        $id = Uuid::uuid7()->toString();

        DB::table('notifications')->insert([
            'id' => $id,
            'recipient_id' => 'user-789',
            'channel' => 'sms',
            'priority' => 'high',
            'status' => 'discarded',
            'message' => 'Failed message',
            'error_message' => 'Provider unavailable',
            'retry_count' => 3,
            'max_retries' => 3,
            'created_at' => now()->format('Y-m-d H:i:s.u'),
        ]);

        $response = $this->getJson("/api/status/{$id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $id,
            'status' => 'discarded',
            'error_message' => 'Provider unavailable',
            'retry_count' => 3,
        ]);
    }

    #[Test]
    public function it_returns_500_for_nonexistent_notification(): void
    {
        $uuid = Uuid::uuid7()->toString();

        $response = $this->getJson("/api/status/{$uuid}");

        $response->assertStatus(500);
        $response->assertJsonStructure(['message']);
    }
}
