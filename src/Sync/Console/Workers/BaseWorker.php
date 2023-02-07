<?php

namespace Sync\Console\Workers;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\BeanstalkConfig;

/**
 * базовый класс для воркеров
 */
abstract class BaseWorker extends Command
{
    /** @var Pheanstalk|null текущее подключение к серверу очередей */
    protected Pheanstalk $connection;

    /** @var string просматриваемая очередь */
    protected string $queue = 'default';

    /**
     * Конструктор BaseWorker
     * @param \Sync\BeanstalkConfig $beanstalk
     */
    final public function __construct(BeanstalkConfig $beanstalk)
    {
        parent::__construct();
        $this->connection = $beanstalk->getConnection();
    }

    /**
     * CLI вызов
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        while (
            $job = $this->connection
            ->watchOnly($this->queue)
            ->ignore(PheanstalkInterface::DEFAULT_TUBE)
            ->reserve()
        ) {
            try {
                $this->process(json_decode(
                    $job->getData(),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ));
            } catch (\Throwable $e) {
                $this->handleException($e, $job);
            }

            $this->connection->delete($job);
        }
        return 0;
    }

    /**
     * @param \Throwable $exception
     * @param Job $job
     * @return void
     */
    private function handleException(\Throwable $exception, Job $job): void
    {
        echo $exception->getMessage() . PHP_EOL . $job->getData();
    }

    /**
     * Обработчик задачи
     * @param $data
     * @return mixed
     */
    abstract public function process($data);
}
