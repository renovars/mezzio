<?php

namespace Sync\Helpers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use App\Models\User;
use League\OAuth2\Client\Token\AccessToken;

class UpdateTokensHelper
{
    /**
     * Обновляет токен авторизации, если его срок истекает раньше переданного времени
     * @param int $time
     * @return void
     * @throws AmoCRMoAuthApiException
     */
    public function updateTokens(int $time)
    {
        $users = User::all();
        foreach ($users as $user) {
            $accessToken = json_decode($user->access_token);
            $accessToken = new AccessToken([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'expires' => $accessToken->expires,
            ]);
            if (mktime($time) >= $accessToken->getExpires()) {
                $apiClient = new AmoCRMApiClient(
                    $user->client_id,
                    $user->client_secret,
                    $user->redirect_uri
                );
                $apiClient->setAccessToken($accessToken)->setAccountBaseDomain($user->base_domain);

                $newAccessToken = $apiClient->getOAuthClient()->getAccessTokenByRefreshToken($accessToken);

                $user->access_token = json_encode($newAccessToken);
                $user->save();
            }
        }
    }
}
