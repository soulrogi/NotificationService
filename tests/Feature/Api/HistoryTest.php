<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_returns_empty_array_when_no_notifications(): void
    {
        $response = $this->getJson('/api/history/recipient/nonexistent-user');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    #[Test]
    public function it_returns_all_notifications_for_recipient(): void
    {
        $recipientId = 'user-42';

        $id1 = Uuid::uuid7()->toString();
        $id2 = Uuid::uuid7()->toString();
        $id3 = Uuid::uuid7()->toString();

        DB::table('notifications')->insert([
            [
                'id' => $id1,
                'recipient_id' => $recipientId,
                'channel' => 'sms',
                'priority' => 'high',
                'status' => 'delivered',
                'message' => 'First message',
                'error_message' => null,
                'retry_count' => 0,
                'max_retries' => 3,
                'created_at' => '2026-06-10 10:00:00.000000',
            ],
            [
                'id' => $id2,
                'recipient_id' => $recipientId,
                'channel' => 'email',
                'priority' => 'low',
                'status' => 'sent',
                'message' => 'Second message',
                'error_message' => null,
                'retry_count' => 1,
                'max_retries' => 3,
                'created_at' => '2026-06-10 11:00:00.000000',
            ],
            [
                'id' => $id3,
                'recipient_id' => $recipientId,
                'channel' => 'sms',
                'priority' => 'low',
                'status' => 'discarded',
                'message' => 'Third message',
                'error_message' => 'Failed to deliver',
                'retry_count' => 3,
                'max_retries' => 3,
                'created_at' => '2026-06-10 12:00:00.000000',
            ],
        ]);

        $response = $this->getJson("/api/history/recipient/{$recipientId}");

        $response->assertStatus(200);
        $response->assertJsonCount(3);

        $response->assertJson([
            [
                'id' => $id1,
                'recipient_id' => $recipientId,
                'status' => 'delivered',
                'message' => 'First message',
            ],
            [
                'id' => $id2,
                'recipient_id' => $recipientId,
                'status' => 'sent',
                'message' => 'Second message',
            ],
            [
                'id' => $id3,
                'recipient_id' => $recipientId,
                'status' => 'discarded',
                'error_message' => 'Failed to deliver',
            ],
        ]);
    }

    #[Test]
    public function it_does_not_return_notifications_for_other_recipients(): void
    {
        $id1 = Uuid::uuid7()->toString();
        $id2 = Uuid::uuid7()->toString();

        DB::table('notifications')->insert([
            [
                'id' => $id1,
                'recipient_id' => 'user-a',
                'channel' => 'sms',
                'priority' => 'low',
                'status' => 'queued',
                'message' => 'For user A',
                'retry_count' => 0,
                'max_retries' => 3,
                'created_at' => now()->format('Y-m-d H:i:s.u'),
            ],
            [
                'id' => $id2,
                'recipient_id' => 'user-b',
                'channel' => 'email',
                'priority' => 'high',
                'status' => 'queued',
                'message' => 'For user B',
                'retry_count' => 0,
                'max_retries' => 3,
                'created_at' => now()->format('Y-m-d H:i:s.u'),
            ],
        ]);

        $response = $this->getJson('/api/history/recipient/user-a');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJson([
            ['id' => $id1, 'recipient_id' => 'user-a'],
        ]);
    }

    #[Test]
    public function it_returns_full_notification_details_in_history(): void
    {
        $id = Uuid::uuid7()->toString();
        $now = now()->format('Y-m-d H:i:s.u');

        DB::table('notifications')->insert([
            'id' => $id,
            'recipient_id' => 'user-full',
            'channel' => 'sms',
            'priority' => 'high',
            'status' => 'delivered',
            'message' => 'Full details',
            'external_id' => 'ext_full_001',
            'error_message' => null,
            'retry_count' => 0,
            'max_retries' => 3,
            'created_at' => $now,
            'updated_at' => $now,
            'sent_at' => $now,
        ]);

        $response = $this->getJson('/api/history/recipient/user-full');

        $response->assertStatus(200);
        $response->assertJsonCount(1);

        $response->assertJson([
            [
                'id' => $id,
                'recipient_id' => 'user-full',
                'channel' => 'sms',
                'priority' => 'high',
                'status' => 'delivered',
                'message' => 'Full details',
                'external_id' => 'ext_full_001',
                'error_message' => null,
                'retry_count' => 0,
                'created_at' => $now,
                'sent_at' => $now,
            ],
        ]);
    }
}
