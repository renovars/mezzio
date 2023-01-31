<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use App\Helpers\SyncHelper;
use App\Models\User;
use Laminas\Diactoros\Response\HtmlResponse;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Хэндлер для синхронизации контактов из amoCRM в Unisender
 */
class SyncHandler implements RequestHandlerInterface
{
    /**
     * @var string API-ключ к вашему кабинету Unisender
     */
    private string $apikey;

    /**
     * @var array данные интеграции amoCRM
     */
    private array $amoCrmUserData;

    public function __construct($amoCrmUserData, $apikey)
    {
        $this->amoCrmUserData = $amoCrmUserData;
        $this->apikey         = $apikey;
    }

    /**
     * Синхронизирует контакты и возвращает список контактов, которые были синхронизированы.
     * Проверяет наличие accessToken в БД по id интеграции, если токена нет, то открывается страница авторизации
     * после авторизации пользователь сохрнатеся в базу, либо обновлется accessToken, если пользователь существует
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws AmoCRMoAuthApiException
     * @throws \AmoCRM\Exceptions\AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $apiClient = new AmoCRMApiClient(
            $this->amoCrmUserData['clientId'],
            $this->amoCrmUserData['clientSecret'],
            $this->amoCrmUserData['redirectUri']
        );
        $syncHelper = new SyncHelper($this->amoCrmUserData, $this->apikey);

        try {
            $accessToken = (json_decode(User::where(
                'client_id',
                $this->amoCrmUserData['clientId']
            )->first()->access_token));
        } catch (\Exception $e) {
            $accessToken = null;
        }

        if (
            isset($accessToken->access_token)
            && isset($accessToken->refresh_token)
            && isset($accessToken->expires)
        ) {
            $accessToken = new AccessToken([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'expires' => $accessToken->expires,
            ]);

            $contacts = $syncHelper->getUserContacts($accessToken, $apiClient);
            $syncHelper->sendToUnisender($contacts);

            return new HtmlResponse(json_encode($contacts, JSON_UNESCAPED_UNICODE));
        } elseif (!isset($_GET['code'])) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth2state'] = $state;
            $authorizationUrl = $apiClient->getOAuthClient()->getAuthorizeUrl([
                'state' => $state,
                'mode' => 'post_message',
            ]);
            header('Location: ' . $authorizationUrl);
            die;
        } elseif (
            empty($_GET['state']) ||
            empty($_SESSION['oauth2state']) ||
            ($_GET['state'] !== $_SESSION['oauth2state'])
        ) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }


        /**
         * Ловим обратный код
         */
        try {
            if (isset($_GET['referer'])) {
                $apiClient->setAccountBaseDomain($_GET['referer']);
            }
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);
            $apiClient->setAccessToken($accessToken);

            $syncHelper->saveUser($accessToken, $apiClient);
        } catch (AmoCRMoAuthApiException $e) {
            exit('Неверный код авторизации, отчистите параметры и перезагрузите страницу');
        }

        $contacts = $syncHelper->getUserContacts($accessToken, $apiClient);
        $syncHelper->sendToUnisender($contacts);

        return new HtmlResponse(json_encode($contacts, JSON_UNESCAPED_UNICODE));
    }
}
