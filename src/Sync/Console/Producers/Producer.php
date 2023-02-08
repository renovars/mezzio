<?php

namespace Sync\Console\Producers;

use Pheanstalk\Pheanstalk;
use Sync\config\BeanstalkConfig;

/**
 * Помещает задачи в очередь
 */
class Producer
{
    /** @var Pheanstalk|null текущее подключение к серверу очередей */
    protected Pheanstalk $connection;

    /**
     * Констуктор Producer
     * @param BeanstalkConfig $beanstalk
     */
    public function __construct(BeanstalkConfig $beanstalk)
    {
        $this->connection = $beanstalk->getConnection();
    }
    /**
     * Отправляет задачу в сервис очередей
     * @param $data
     * @param string $queueName
     * @return void
     * @throws \Exception
     */
    public function addToQueue($data, string $queueName)
    {
        try {
            $job = $this->connection;
            $job->useTube($queueName)->put(json_encode($data));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
