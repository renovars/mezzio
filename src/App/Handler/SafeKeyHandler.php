<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use App\Helpers\SyncHelper;
use App\Models\User;
use Illuminate\Database\QueryException;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Хэндлер сохранения ключа Unisender
 */
class SafeKeyHandler implements RequestHandlerInterface
{
    /**
     * @var array данные интеграции amoCRM
     */
    private array $amoCrmUserData;

    public function __construct($amoCrmUserData)
    {
        $this->amoCrmUserData = $amoCrmUserData;
    }

    /**
     * получает id аккаунта и ключ из запроса и сохраняет в БД,
     * синхронизирует контакты из amoCRM в Unisender
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $accountId = $request->getParsedBody()['account_id'] ?? null;
            $apiKey = $request->getParsedBody()['token'] ?? null;
            $user = User::where('account_id', $accountId)->first();
            if (isset($user)) {
                $user->api_key = $apiKey;
                $user->save();

                $syncHelper = new SyncHelper($this->amoCrmUserData);
                $contacts = $syncHelper->getUserContacts();
                $syncHelper->sendToUnisender($contacts);
                $syncHelper->subscribe();
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('Неверные аргументы');
        } catch (QueryException $e) {
            throw new \Exception('Нет доступа к базе');
        } catch (\Exception | \TypeError $e) {
            throw new \Exception('Неизвестная ошибка');
        }

        return new HtmlResponse(json_encode($user));
    }
}
