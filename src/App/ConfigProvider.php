<?php

declare(strict_types=1);

namespace App;

use App\Factory\HomePageHandlerFactory;
use App\Factory\SafeKeyHandlerFactory;
use App\Factory\SafeUserHandlerFactory;
use App\Handler\HomePageHandler;
use App\Handler\SafeKeyHandler;
use App\Handler\SumHandler;
use App\Handler\SafeUserHandler;
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
                WebhooksHandler::class => WebhooksHandler::class,
            ],
            'factories'  => [
                HomePageHandler::class => HomePageHandlerFactory::class,
                SafeKeyHandler::class => SafeKeyHandlerFactory::class,
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
}
