<?php

namespace Sync\config;

use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;

/**
 * Подключение к базе данных
 */
class DataBaseConnection
{
    /** @var DataBaseConnection|null DBConnection */
    private static ?DataBaseConnection $DBConnection = null;

    /** Приватный констуктор чтобы нельзя было создать объекты извне*/
    private function __construct()
    {
    }

    /**
     * Создает подключение к БД
     * @param ContainerInterface $container
     * @return DataBaseConnection|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function getConnection(ContainerInterface $container): ?DataBaseConnection
    {
        if (self::$DBConnection === null) {
            $capsule = new Manager();
            $capsule->addConnection($container->get('config')['eloquent']);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            self::$DBConnection = new DataBaseConnection();
        }

        return self::$DBConnection;
    }
}
