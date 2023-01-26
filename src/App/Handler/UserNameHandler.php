<?php

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Filters\ContactsFilter;
use Laminas\Diactoros\Response\HtmlResponse;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;

define('TOKEN_FILE', DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'token_info.json');

class UserNameHandler implements RequestHandlerInterface
{
    /**
     * Синхронизирует контакты и вывод список контактов, которые были синхронизированы.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     * @throws \AmoCRM\Exceptions\BadTypeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $apikey = '63m6y3xxrj8zkunpu4s7xdjffcpoahdoe9cmk7qo'; //API-ключ к вашему кабинету

        $clientId = '31c19411-32df-492e-b608-88bb7fcfa212';
        $clientSecret = 'rYsLOWqEKI1SjzK1RbPK4ZLseVdrdq7vTLBL6p883sde3hZlUo3vMpTWPnL5nwa4';
        $redirectUri = 'https://3990-173-233-147-68.eu.ngrok.io/api/user';

        $apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);

        if (isset($_GET['referer'])) {
            $apiClient->setAccountBaseDomain($_GET['referer']);
        }

        if (file_exists(TOKEN_FILE)) {
            $accessToken = json_decode(file_get_contents(TOKEN_FILE));
        }
        if (
            isset($accessToken->accessToken)
            && isset($accessToken->refreshToken)
            && isset($accessToken->expires)
        ) {
            $accessToken = new AccessToken([
                'access_token' => $accessToken->accessToken,
                'refresh_token' => $accessToken->refreshToken,
                'expires' => $accessToken->expires,
            ]);

            $contacts = $this->getUserContacts($accessToken, $apiClient);

            $importParams = [
                'field_names' => ['email', 'Name'],
                'data' => $contacts,
            ];

            $uni = new UnisenderApi($apikey);
            $uni->importContacts($importParams);
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
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);
            $this->saveToken($accessToken);
        } catch (AmoCRMoAuthApiException $e) {
            exit('Неверный код авторизации, отчистите параметры и перезагрузите страницу');
        }

        $contacts = $this->getUserContacts($accessToken, $apiClient);
        $importParams = [
            'field_names' => ['email', 'Name'],
            'data' => $contacts,
        ];

        $uni = new UnisenderApi($apikey);
        $uni->importContacts($importParams);

        return new HtmlResponse(json_encode($contacts, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Получает список контактов из amoCRM и сохраняет их email и имя в массив
     *
     * @param AccessTokenInterface $accessToken
     * @param AmoCRMApiClient $apiClient
     * @return array
     * @throws \AmoCRM\Exceptions\AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     */
    private function getUserContacts(AccessTokenInterface $accessToken, AmoCRMApiClient $apiClient): array
    {
        try {
            if ($accessToken->hasExpired()) {
                throw new \Exception('Вышел срок действия токена');
            }
            $apiClient->setAccessToken($accessToken)->setAccountBaseDomain('novars.amocrm.ru');

            $contacts = $apiClient->contacts()->get()->all();

            $contactsNameAndEmail = [];
            foreach ($contacts as $contact) {
                $customFields = $contact->getCustomFieldsValues();
                $emailField = $customFields->getBy('fieldCode', 'EMAIL');
                $email = $emailField->values[0]->value;

                if (isset($email)) {
                    $contactsNameAndEmail[] = [
                        $email,
                        $contact->name,
                    ];
                }
            }
        } catch (AmoCRMApiNoContentException $e) {
            echo 'Нет контактов в amoCRM';
            die;
        } catch (AmoCRMoAuthApiException $e) {
            echo 'Нужна повторная авторизация, обновите страницу';
            unlink(TOKEN_FILE);
            ob_end_flush();
            die;
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
        }
        return $contactsNameAndEmail;
    }

    /**
     * @param AccessTokenInterface $accessToken
     */
    private function saveToken(AccessTokenInterface $accessToken)
    {

        $data = [
            'accessToken' => $accessToken->getToken(),
            'refreshToken' => $accessToken->getRefreshToken(),
            'expires' => $accessToken->getExpires(),
            ];

        file_put_contents(TOKEN_FILE, json_encode($data));
    }
}
