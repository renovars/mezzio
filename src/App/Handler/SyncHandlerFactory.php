<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;

/**
 * Создает SyncHandler
 */
class SyncHandlerFactory
{//todo if we use some information for multiple times we can add them to the configurations file, and we can get to those information using the container interface
    /**
     * @var string API-ключ к вашему кабинету Unisender
     */
    private string $apikey = '63m6y3xxrj8zkunpu4s7xdjffcpoahdoe9cmk7qo';

    /**
     * @var array данные интеграции amoCRM
     */
    private array $amoCrmUserData = [
        'clientId'     => '31c19411-32df-492e-b608-88bb7fcfa212',
        'clientSecret' => 'rYsLOWqEKI1SjzK1RbPK4ZLseVdrdq7vTLBL6p883sde3hZlUo3vMpTWPnL5nwa4',
        'redirectUri'  => 'https://3990-173-233-147-68.eu.ngrok.io/api/sync',
        ];

    /**
     * Задает параметры для авторизации в сервисах
     *
     * @param ContainerInterface $container
     * @return SyncHandler
     */
    public function __invoke(ContainerInterface $container): SyncHandler
    {
        return new SyncHandler($this->amoCrmUserData, $this->apikey);
    }
}
