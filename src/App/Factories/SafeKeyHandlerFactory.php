<?php

declare(strict_types=1);

namespace App\Factories;

use App\Handler\SafeKeyHandler;
use Psr\Container\ContainerInterface;

/**
 * Фабрика хэндлера сохранения токена Unisender
 */
class SafeKeyHandlerFactory
{
    /**
     * Задает параметры для авторизации в сервисах
     *
     * @param ContainerInterface $container
     * @return SafeKeyHandler
     */
    public function __invoke(ContainerInterface $container): SafeKeyHandler
    {
        $amoCrmUserData = $container->get('config')['authorization'];
        return new SafeKeyHandler($amoCrmUserData);
    }

}