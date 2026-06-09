<?php

declare(strict_types=1);

return [
    'host' => env(
        key: 'KAFKA_HOST',
        default: 'kafka',
    ),
    'port' => env(
        key: 'KAFKA_PORT',
        default: '9092',
    ),
];
