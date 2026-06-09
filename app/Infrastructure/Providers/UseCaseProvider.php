<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\UseCase\AddMessage\AddMessage;
use App\Application\UseCase\AddMessage\AddMessageUseCase;
use App\Application\UseCase\AddMessage\IdempotentAddMessageDecorator;
use App\Infrastructure\Service\IdempotentWrapperImpl;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class UseCaseProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            AddMessageUseCase::class,
            function (Application $app): AddMessageUseCase {
                /** @var AddMessageUseCase $sendUseCase */
                $sendUseCase = $app->get(AddMessage::class);

                /** @var IdempotentWrapperImpl $idempotentWrapper */
                $idempotentWrapper = $app->get(IdempotentWrapperImpl::class);

                return new IdempotentAddMessageDecorator(
                    useCase: $sendUseCase,
                    idempotentWrapper: $idempotentWrapper,
                );
            },
        );
    }
}
