<?php

namespace Sync\Factories\WorkersFactories;

use Psr\Container\ContainerInterface;
use Sync\config\BeanstalkConfig;
use Sync\Console\Workers\TimeWorker;

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
