<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Фабрика для хэндлера домашней страницы
 */
class HomePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {   echo "<pre>";
        var_dump($container);
        echo "</pre>";
        exit();
        return new HomePageHandler();
    }
}