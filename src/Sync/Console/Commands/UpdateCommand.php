<?php

namespace Sync\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\config\DataBaseConnection;
use Sync\Helpers\UpdateTokensHelper;

/**
 * Команда для обновления токенов авторизации
 */
class UpdateCommand extends Command
{
    /** @var string Имя команды */
    protected static $defaultName = 'update-command';

    /** @var DataBaseConnection|null подключение к БД */
    protected ?DataBaseConnection $DBConnecton = null;

    public function __construct(DataBaseConnection $DBConnecton)
    {
        parent::__construct();
        $this->DBConnecton = $DBConnecton;
    }

    /**
     * Задает параметры команды
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName);
        $this->addOption('time', 't', InputOption::VALUE_REQUIRED);
    }

    /**
     * Выполняет команду
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $time = $input->getOption('time');
        $helper = new UpdateTokensHelper();
        $helper->updateTokens($time);

        return 0;
    }
}
