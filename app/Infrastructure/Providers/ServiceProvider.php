<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\Service\IdempotentWrapper;
use App\Application\Service\Synchronization\MutexFactory;
use App\Domain\Service\Dispatcher;
use App\Domain\Service\Sender\Sender;
use App\Domain\Service\Sender\SenderProvider;
use App\Infrastructure\Service\DispatcherImpl;
use App\Infrastructure\Service\IdempotentWrapperImpl;
use App\Infrastructure\Service\Sender\EmailSender;
use App\Infrastructure\Service\Sender\SmsSender;
use App\Infrastructure\Service\Synchronization\Driver\RedisMutexFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as ServiceProviderBase;

class ServiceProvider extends ServiceProviderBase
{
    public function register(): void
    {
        $this->app->singleton(
            Dispatcher::class,
            DispatcherImpl::class,
        );

        $this->app->singleton(
            IdempotentWrapper::class,
            IdempotentWrapperImpl::class,
        );

        $this->app->singleton(
            MutexFactory::class,
            RedisMutexFactory::class,
        );

        $this->app->singleton(
            SenderProvider::class,
            function (Application $app) {
                /** @var Sender $email */
                $email = $app->get(EmailSender::class);

                /** @var Sender $sms */
                $sms = $app->get(SmsSender::class);

                return new SenderProvider(senders: [
                    $email,
                    $sms,
                ]);
            },
        );
    }
}
