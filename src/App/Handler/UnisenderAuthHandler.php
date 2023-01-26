<?php

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;

class UnisenderAuthHandler implements RequestHandlerInterface
{
    /**
     * Получает контакт из Unisender по email и вывод на экран.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $apikey = '63m6y3xxrj8zkunpu4s7xdjffcpoahdoe9cmk7qo'; //API-ключ к вашему кабинету
        $uni = new UnisenderApi($apikey); //создаем экземпляр класса

        $params = [
            'email'   => 'novikov.arsenii@gmail.com',
            'api_key' => $apikey
        ];
        $user = $uni->getContact($params);

        $importParams = [
            'field_names' => ['email', 'Name'],
            'data' => [
                ['sergey@yandex.ru', 'Sergey'],
                ['exampe@mail.ru', 'Anton'],
            ]
        ];

        $result = $uni->importContacts($importParams);

        return new HtmlResponse($result);
    }
}
