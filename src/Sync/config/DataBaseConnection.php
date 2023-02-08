<?php

namespace Sync\config;

use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;

/**
 * Подключение к базе данных
 */
class DataBaseConnection
{
    public function __construct(ContainerInterface $container)
    {
        $capsule = new Manager();
        $capsule->addConnection($container->get('config')['eloquent']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
