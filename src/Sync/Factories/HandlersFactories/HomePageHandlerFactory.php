<?php

declare(strict_types=1);

namespace Sync\Factories\HandlersFactories;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\config\BeanstalkConfig;
use Sync\Handler\HomePageHandler;

/**
 * Фабрика для хэндлера домашней страницы
 */
class HomePageHandlerFactory
{
    /**
     * Передает конфиг для подключения к серверу очередей
     * @param ContainerInterface $container
     * @return RequestHandlerInterface
     */
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new HomePageHandler(new BeanstalkConfig($container));
    }
}
