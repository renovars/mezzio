<?php

declare(strict_types=1);

namespace App\Handler;

use App\Models\User;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Хэндлер сохранения ключа Unisender
 */
class SafeKeyHandler implements RequestHandlerInterface
{
    /**
     * получает id аккаунта и ключ из запроса и сохраняет в БД
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        //todo exception handling or error handling

        $accountId = $request->getParsedBody()['account_id'];
        $apiKey = $request->getParsedBody()['token'];
        //todo exception handling exception + error or throwables 
        $user = User::where('account_id', $accountId)->first();
        if (isset($user)) {
            $user->api_key = $apiKey;
            $user->save();
        } elseif (isset($accountId)) {
            $user = new User();
            $user->api_key = $apiKey;
            $user->account_id = $accountId;
            $user->save();
        }
//todo first sync , connect to webhook
        return new HtmlResponse('');
    }
}
