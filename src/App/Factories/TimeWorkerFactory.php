<?php

namespace App\Factories;

use App\BeanstalkConfig;
use App\Console\Workers\TimeWorker;
use Psr\Container\ContainerInterface;

/**
 * Фабрика для воркера очереди times
 */
class TimeWorkerFactory
{
    /**
     * Добавляет параметры подключения к сереверу очередей
     * @param ContainerInterface $container
     * @return TimeWorker
     */
    public function __invoke(ContainerInterface $container): TimeWorker
    {
        return new TimeWorker(new BeanstalkConfig($container));
    }
}
