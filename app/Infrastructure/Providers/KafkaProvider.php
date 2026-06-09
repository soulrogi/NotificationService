<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Infrastructure\Kafka\Config;
use Illuminate\Support\ServiceProvider;

class KafkaProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Config::class, function () {
            /** @var string $host */
            $host = config('kafka.host');

            /** @var string $port */
            $port = config('kafka.port');

            return new Config($host . ':' . $port);
        });
    }
}
