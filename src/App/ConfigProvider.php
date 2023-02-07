<?php

declare(strict_types=1);

namespace App;

use App\Console\Commands\HowTimeCommand;
use App\Console\Workers\TimeWorker;
use App\Factories\HomePageHandlerFactory;
use App\Factories\SafeKeyHandlerFactory;
use App\Factories\SafeUserHandlerFactory;
use App\Factories\TimeWorkerFactory;
use App\Handler\HomePageHandler;
use App\Handler\SafeKeyHandler;
use App\Handler\SafeUserHandler;
use App\Handler\SumHandler;
use App\Handler\WebhooksHandler;

/**
 * The configuration provider for the App module
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
            'templates'    => $this->getTemplates(),
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
                SumHandler::class      => SumHandler::class,
                WebhooksHandler::class => WebhooksHandler::class,
            ],
            'factories'  => [
                TimeWorker::class => TimeWorkerFactory::class,
                HomePageHandler::class => HomePageHandlerFactory::class,
                SafeKeyHandler::class  => SafeKeyHandlerFactory::class,
                SafeUserHandler::class => SafeUserHandlerFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'    => ['templates/app'],
                'error'  => ['templates/error'],
                'layout' => ['templates/layout'],
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
