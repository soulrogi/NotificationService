<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Repository\ReadNotifications;
use App\Domain\Repository\WriteNotifications;
use App\Infrastructure\Repository\ReadNotificationsImpl;
use App\Infrastructure\Repository\WriteNotificationsImpl;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            WriteNotifications::class,
            WriteNotificationsImpl::class,
        );

        $this->app->singleton(
            ReadNotifications::class,
            ReadNotificationsImpl::class,
        );
    }
}
