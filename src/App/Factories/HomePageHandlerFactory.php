<?php

declare(strict_types=1);

namespace App\Factories;

use App\Handler\HomePageHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Фабрика для хэндлера домашней страницы
 */
class HomePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new HomePageHandler();
    }
}
