<?php

namespace App\Console\Workers;

/**
 * Воркер очереди times
 */
class TimeWorker extends BaseWorker
{
    /** Имя просматриваемой очереди */
    protected string $queue = 'times';

    /** Обработчик задач из очереди */
    public function process($data)
    {
        echo $data . PHP_EOL;
    }
}
