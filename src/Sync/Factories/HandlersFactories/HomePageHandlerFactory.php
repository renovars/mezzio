<?php

declare(strict_types=1);

namespace Sync\Factories\HandlersFactories;

use Sync\Handler\HomePageHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\BeanstalkConfig;

/**
 * Фабрика для хэндлера домашней страницы
 */
class HomePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new HomePageHandler(new BeanstalkConfig($container));
    }
}
