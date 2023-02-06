<?php

namespace App\Console\Workers;

use Pheanstalk\Pheanstalk;

/**
 * Обработчик задач из очереди times
 */
class TimeWorker
{
    /**
     * Имя просматриваемой очереди
     */
    public const QUEUE = 'times';

    /**
     * Обработчик задач из очереди
     * @return mixed
     */
    public function process()
    {
        try {
            while (true) {
                //Создаем подключение к очереди
                $pheanstalk = Pheanstalk::create('localhost')
                    ->watch(self::QUEUE);

                //Резервируем последнюю задачу
                $job = $pheanstalk->reserve();

                //Выводим данные задачи
                echo json_decode($job->getData(), true) . PHP_EOL;

                //Удаляем задачу
                $pheanstalk->delete($job);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
