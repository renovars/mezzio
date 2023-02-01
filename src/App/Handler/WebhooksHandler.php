<?php

declare(strict_types=1);

namespace App\Handler;

use App\Models\User;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;

/**
 * Хэндлер обновления контактов в Unisender при изменении в amoCRM
 */
class WebhooksHandler implements RequestHandlerInterface
{
    /**
     * Получает данные из POST запроса и отправляет в Unisender
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        //todo check your data first
        $requestParams = $request->getParsedBody();
        $accountId = $requestParams['account']['id'];
        $apiKey = User::where('account_id', $accountId)->first()->api_key;

        $contactName  = $requestParams['contacts']['add'][0]['name'] ?? $requestParams['contacts']['update'][0]['name'];
        $contactEmail = $requestParams['contacts']['add'][0]['custom_fields'][0]['values'][0]['value']
            ?? $requestParams['contacts']['update'][0]['custom_fields'][0]['values'][0]['value'];

        $importParams = [
            'field_names' => ['email', 'Name'],
            'data' => [[$contactEmail, $contactName]],
        ];
       //todo exception handling
        $uni = new UnisenderApi($apiKey);
        $unisenderResponse = $uni->importContacts($importParams);

        return new HtmlResponse($unisenderResponse);
    }
}
