<?php

namespace App\Helpers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\WebhookModel;
use App\Models\User;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Unisender\ApiWrapper\UnisenderApi;

define('LOG_FILE', DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'log.txt');

/**
 * Класс с методами для работы хэндлера авторизации
 */
class SyncHelper
{
    /**
     * @var AmoCRMApiClient клиент amoCRM
     */
    private AmoCRMApiClient $apiClient;

    /**
     * @var string ключ Unisender
     */
    private ?string $apiKey;

    /**
     * @var array данные интеграции amoCRM
     */
    private array $amoCrmUserData;

    public function __construct(array $amoCrmUserData)
    {
        $this->amoCrmUserData = $amoCrmUserData;
        $this->apiClient = new AmoCRMApiClient(
            $amoCrmUserData['clientId'],
            $amoCrmUserData['clientSecret'],
            $amoCrmUserData['redirectUri']
        );
        try {
            $this->apiClient->setAccountBaseDomain($this->getBaseDomain())->setAccessToken($this->getAccessToken());
        } catch (\Exception | \TypeError $e) {
        }
        $this->apiKey = User::where(
            'client_id',
            $this->amoCrmUserData['clientId']
        )->first()->api_key;
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
    public function getUserContacts(): array
    {
        try {
            if ($this->getAccessToken()->hasExpired()) {
                $this->deleteToken();
                throw new \Exception('Вышел срок действия токена');
            }
            $this->apiClient->setAccountBaseDomain($this->getBaseDomain())->setAccessToken($this->getAccessToken());

            $contacts = $this->apiClient->contacts()->get()->all();

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
            return [];
        } catch (AmoCRMoAuthApiException $e) {
            echo 'Нужна повторная авторизация, обновите страницу';
            $this->deleteToken();
            return [];
        }
        return $contactsNameAndEmail;
    }

    /**
     * Сохраняет пользователя в БД
     *
     * @param AccessTokenInterface $accessToken
     * @param AmoCRMApiClient $apiClient
     * @return void
     * @throws AmoCRMoAuthApiException
     * @throws \AmoCRM\Exceptions\AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     */
    public function saveUser(AmoCRMApiClient $apiClient, AccessTokenInterface $accessToken)
    {
        $user = User::where('account_id', $apiClient->account()->getCurrent()->getId())->first();
        if (!isset($user)) {
            $user = new User();
        }
        $user->name          = $apiClient->users()->get()->all()[0]->name;
        $user->base_domain   = $apiClient->getAccountBaseDomain();
        $user->client_id     = $this->amoCrmUserData['clientId'];
        $user->client_secret = $this->amoCrmUserData['clientSecret'];
        $user->redirect_uri  = $this->amoCrmUserData['redirectUri'];
        $user->access_token  = json_encode($accessToken);
        $user->api_key       = $this->apiKey;
        $user->account_id    = $apiClient->account()->getCurrent()->getId();

        $user->save();
    }

    /**
     * отправляет данные в Unisender и сохраняет ответ в log.txt
     *
     * @param array $contacts
     * @return void
     */
    public function sendToUnisender(array $contacts)
    {
        $importParams = [
            'field_names' => ['email', 'Name'],
            'data' => $contacts,
        ];

        $uni = new UnisenderApi($this->apiKey);
        $unisenderResponse = $uni->importContacts($importParams);
        file_put_contents(LOG_FILE, date("Y-m-d H:i:s") . $unisenderResponse, FILE_APPEND);
    }

    /**
     * Удаляет токен пользователя
     *
     * @return void
     */
    public function deleteToken()
    {
        $user = User::where('client_id', $this->amoCrmUserData['clientId'])->first();
        $user->access_token = null;
        $user->save();
    }

    /**
     * Возвращает домен аккаунта
     *
     * @return string
     */
    public function getBaseDomain(): ?string
    {
        $user = User::where('client_id', $this->amoCrmUserData['clientId'])->first();
        return $user->base_domain;
    }

    /**
     * @return AccessTokenInterface
     */
    public function getAccessToken(): ?AccessTokenInterface
    {
        $user = User::where('client_id', $this->amoCrmUserData['clientId'])->first();
        $accessToken = json_decode($user->access_token);

            return new AccessToken([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'expires' => $accessToken->expires,
            ]);
    }

    /**
     * Добавляет подписку на webhooks для аккаунта
     *
     * @param AmoCRMApiClient $apiClient
     * @return void
     * @throws AmoCRMoAuthApiException
     * @throws \AmoCRM\Exceptions\AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     */
    public function subscribe()
    {
        $webHookModel = (new WebhookModel())
            ->setSettings(['add_contact', 'update_contact'])
            ->setDestination('https://3990-173-233-147-68.eu.ngrok.io/api/webhooks');

        $this->apiClient->webhooks()->subscribe($webHookModel);
    }
}
