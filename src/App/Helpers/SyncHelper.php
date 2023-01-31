<?php

namespace App\Helpers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
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
    private array $amoCrmUserData;

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

            $apiClient->setAccountBaseDomain($this->getBaseDomain())->setAccessToken($accessToken);

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
            $this->deleteToken();
            die;
        } catch (\Exception $e) {
            echo $e->getMessage();
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
        try {
            $user = User::where('client_id', $this->amoCrmUserData['clientId'])->first();
            $user->access_token = json_encode($accessToken);
            $user->save();
        } catch (\Exception $e) {
            $user = User::create([
                'name' => $apiClient->users()->get()->all()[0]->name,
                'base_domain' => $apiClient->getAccountBaseDomain(),
                'client_id' => $this->amoCrmUserData['clientId'],
                'client_secret' => $this->amoCrmUserData['clientSecret'],
                'redirect_uri' => $this->amoCrmUserData['redirectUri'],
                'access_token' => json_encode($accessToken),
                'api_key' => $this->apiKey,
            ]);
        }
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
}
