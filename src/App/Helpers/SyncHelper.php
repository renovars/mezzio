<?php

namespace App\Helpers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\WebhookModel;
use App\Models\User;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Unisender\ApiWrapper\UnisenderApi;

define('LOG_FILE', DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'log.txt');

/**
 * Класс с методами для работы хэндлера авторизации
 */
class SyncHelper
{
    /**
     * @var array данные интеграции amoCRM
     */
    private ?array $amoCrmUserData;

    /**
     * @var string ключ Unisender
     */
    private string $apiKey;

    public function __construct(array $amoCrmUserData, string $apiKey)
    {
        $this->amoCrmUserData = $amoCrmUserData;
        $this->apiKey = $apiKey;
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
    public function getUserContacts(AccessTokenInterface $accessToken, AmoCRMApiClient $apiClient): array
    {
        try {
            if ($accessToken->hasExpired()) {
                $this->deleteToken();
                throw new \Exception('Вышел срок действия токена');
            }
            $apiClient->setAccountBaseDomain($this->getBaseDomain());

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
            //todo return []
            //todo hellper abstraction using throw exception 
            die;
        } catch (AmoCRMoAuthApiException $e) {
            echo 'Нужна повторная авторизация, обновите страницу';
            $this->deleteToken();
            die;
        } catch (\Exception $e) {
            echo 'Неизвестная ошибка';
            die;
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
    public function saveUser(AccessTokenInterface $accessToken, AmoCRMApiClient $apiClient)
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
    public function getBaseDomain(): string
    {
        $user = User::where('client_id', $this->amoCrmUserData['clientId'])->first();
        return $user->base_domain;
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
    public function subscribe(AmoCRMApiClient $apiClient)
    {
        $webHookModel = (new WebhookModel())
            ->setSettings(['add_contact', 'update_contact'])
            ->setDestination('https://3990-173-233-147-68.eu.ngrok.io/api/webhooks');

        $apiClient->webhooks()->subscribe($webHookModel);
    }
}
