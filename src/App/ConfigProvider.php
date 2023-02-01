<?php

declare(strict_types=1);

namespace App;

use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use App\Handler\SumHandler;
use App\Handler\SyncHandler;
use App\Handler\SyncHandlerFactory;
use App\Handler\SafeKeyHandler;
use App\Handler\SafeKeyHandlerFactory;
use Whoops\Handler\Handler;

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
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                SumHandler::class => SumHandler::class,
                SafeKeyHandler::class => SafeKeyHandler::class,
            ],
            'factories'  => [
                HomePageHandler::class => HomePageHandlerFactory::class,
                SyncHandler::class => SyncHandlerFactory::class,
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
}
