<?php

namespace Sync\Factories\ProducersFactories;

use Psr\Container\ContainerInterface;
use Sync\BeanstalkConfig;
use Sync\Console\Producers\Producer;

/**
 * Фабрика для продюсера очереди times
 */
class ProducerFactory
{
    /**
     * Добавляет параметры подключения к сереверу очередей
     * @return Producer
     */
    public function getProducer(): Producer
    {
        /** @var ContainerInterface $container */
        $container = require 'config/container.php';
        return new Producer(new BeanstalkConfig($container));
    }
}
