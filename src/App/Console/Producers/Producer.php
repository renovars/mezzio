<?php

namespace App\Console\Producers;

use Pheanstalk\Pheanstalk;

/**
 * Помещает задачи в очередь
 */
class Producer
{
    /**
     * Отправляет задачу в сервис очередей
     * @param $data
     * @param string $queueName
     * @return void
     * @throws \Exception
     */
    public static function addToQueue($data, string $queueName)
    {
        try {
            $job = Pheanstalk::create('localhost', 11300);
            $job->useTube($queueName)->put(json_encode($data));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
