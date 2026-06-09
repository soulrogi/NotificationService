<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('recipient_id');
            $table->string('channel', 10);
            $table->string('priority', 6);
            $table->string('status', 20);
            $table->text('message');
            $table->string('external_id')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->unsignedTinyInteger('max_retries')->default(3);
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->nullable();
            $table->timestamp('sent_at', 6)->nullable();

            $table->index('recipient_id');
            $table->index('status');
            $table->index(['status', 'retry_count', 'max_retries']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
