<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Класс консольной команды выводящей текущее время
 */
class HowTimeCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'how-time';

    /**
     * Задает параметры команды
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName);
    }

    /**
     * Выполняет команду
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        echo Carbon::now()->format('H:i:s (m.Y)');
        return 0;
    }
}
