<?php

declare(strict_types=1);

namespace Sync\Handler;

use Carbon\Carbon;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\BeanstalkConfig;
use Sync\Console\Producers\Producer;

/**
 * Хэндлер домашней страницы
 */
class HomePageHandler implements RequestHandlerInterface
{
    /** @var BeanstalkConfig Конфигурация подключения */
    protected BeanstalkConfig $config;

    /**
     * Констуктор HomePageHandler
     * @param BeanstalkConfig $beanstalk
     */
    public function __construct(BeanstalkConfig $beanstalk)
    {
        $this->config = $beanstalk;
    }
    /**
     * Возвращает текст приветствия
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $target = $request->getQueryParams()['target'] ?? 'World';
        $target = htmlspecialchars($target, ENT_HTML5, 'UTF-8');

        //Отправляем задачу в очередь
        $data = Carbon::now()->format('H:i:s (m.Y)');

        $producer = new Producer($this->config);
        $producer->addToQueue($data, 'times');

        return new HtmlResponse(sprintf(
            '<h1>Hello %s</h1>',
            $target
        ));
    }
}
