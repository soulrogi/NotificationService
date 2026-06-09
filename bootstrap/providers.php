<?php

declare(strict_types=1);

use App\Infrastructure\Providers\KafkaProvider;
use App\Infrastructure\Providers\RepositoryProvider;
use App\Infrastructure\Providers\ServiceProvider;
use App\Infrastructure\Providers\UseCaseProvider;

return [
    RepositoryProvider::class,
    UseCaseProvider::class,
    KafkaProvider::class,
    ServiceProvider::class
];
