<?php

declare(strict_types=1);

namespace App\Factories;

use App\Handler\SafeUserHandler;
use Psr\Container\ContainerInterface;

/**
 * Создает SafeUserHandler
 */
class SafeUserHandlerFactory
{
    /**
     * Задает параметры для авторизации в сервисах
     *
     * @param ContainerInterface $container
     * @return SafeUserHandler
     */
    public function __invoke(ContainerInterface $container): SafeUserHandler
    {
        $amoCrmUserData = $container->get('config')['authorization'];
        return new SafeUserHandler($amoCrmUserData);
    }
}
