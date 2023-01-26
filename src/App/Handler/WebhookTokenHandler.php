<?php

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\BadTypeException;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WebhookTokenHandler implements RequestHandlerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws AmoCRMoAuthApiException
     * @throws BadTypeException|Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $clientId     = '891fa943-2fa6-4af0-a827-b43a5dc352ed';
        $clientSecret = 'DScVmR2BFp0JW31f0aHoFgth6acjEfR70ijfvgDsFrh09oJSRSF2Z44DwYnJu1m8';
        $redirectUri  = '';

        $apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);

        if (isset($_GET['referer'])) {
            $apiClient->setAccountBaseDomain($_GET['referer']);
        }

        if (!isset($_GET['code'])) {
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

        return new HtmlResponse('Hello, %s!');
    }
}
