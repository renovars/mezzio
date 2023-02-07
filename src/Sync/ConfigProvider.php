<?php

declare(strict_types=1);

namespace Sync;

use Sync\Console\Commands\HowTimeCommand;
use Sync\Console\Workers\TimeWorker;
use Sync\Factories\WorkersFactories\TimeWorkerFactory;

/**
 * The configuration provider for the Sync module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'laminas-cli'  => $this->getCliConfig(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
            ],
            'factories'  => [
                TimeWorker::class => TimeWorkerFactory::class,
            ],
        ];
    }

    /**
     * return cli-commands list
     */
    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'package:how-time' => HowTimeCommand::class,
                'package:times' => TimeWorker::class,
            ],
        ];
    }
}
