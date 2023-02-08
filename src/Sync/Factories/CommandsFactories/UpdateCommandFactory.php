<?php

namespace Sync\Factories\CommandsFactories;

use Psr\Container\ContainerInterface;
use Sync\config\DataBaseConnection;
use Sync\Console\Commands\UpdateCommand;

/**
 * Фабрика для команды обновления токенов
 */
class UpdateCommandFactory
{
    /**
     * Создает подключение к БД и передает в конструктор
     * @param ContainerInterface $container
     * @return UpdateCommand
     */
    public function __invoke(ContainerInterface $container): UpdateCommand
    {
        $DBConnection = new DataBaseConnection($container);
        return new UpdateCommand($DBConnection);
    }
}