<?php

namespace App;

use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Конфиг для подключения к серверу очередей
 */
class BeanstalkConfig
{
    /** @var Pheanstalk|null Подлкючение к серверу очередей */
    private ?Pheanstalk $connection;

    /** @var array|mixed Параметры подключения */
    private array $config;

    /**
     * Конструктор
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        try {
            $this->config = $container->get('config')['beanstalk'];
            $this->connection = Pheanstalk::create(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Возвращает подключение к серверу очередей
     * @return Pheanstalk|null
     */
    public function getConnection(): ?Pheanstalk
    {
        return $this->connection;
    }
}