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
     */
    public static function addToQueue($data, string $queueName)
    {
        //Отправляем задачу в очередь
        $job = Pheanstalk::create('localhost', 11300);
        $job->useTube($queueName)->put(json_encode($data));
    }
}
