<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Хэндлер домашней страницы
 */
class HomePageHandler implements RequestHandlerInterface
{
    /**
     * Возвращает текст приветствия
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $target = $request->getQueryParams()['target'] ?? 'World';
        $target = htmlspecialchars($target, ENT_HTML5, 'UTF-8');
        return new HtmlResponse(sprintf(
            '<h1>Hello %s</h1>',
            $target
        ));
    }
}
