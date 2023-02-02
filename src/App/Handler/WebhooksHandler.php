<?php

declare(strict_types=1);

namespace App\Handler;

use App\Models\User;
use Illuminate\Database\QueryException;
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
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $requestParams = $request->getParsedBody();
            $accountId = $requestParams['account']['id'] ?? null;
            $apiKey = User::where('account_id', $accountId)->first()->api_key;

            $contactName = ($requestParams['contacts']['add'][0]['name'] ?? $requestParams['contacts']['update'][0]['name']) ?? null;
            $contactEmail = ($requestParams['contacts']['add'][0]['custom_fields'][0]['values'][0]['value']
                    ?? $requestParams['contacts']['update'][0]['custom_fields'][0]['values'][0]['value'])
                ?? null;

            $importParams = [
                'field_names' => ['email', 'Name'],
                'data' => [[$contactEmail, $contactName]],
            ];
            $uni = new UnisenderApi($apiKey);
            $unisenderResponse = $uni->importContacts($importParams);
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('Неверные аргументы');
        } catch (QueryException $e) {
            throw new \Exception('Нет доступа к базе');
        } catch (\Exception | \TypeError $e) {
            throw new \Exception('Неизвестная ошибка');
        }

        return new HtmlResponse($unisenderResponse);
    }
}
