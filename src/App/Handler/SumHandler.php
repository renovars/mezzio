<?php

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SumHandler implements RequestHandlerInterface
{

    /**
     * returns sum of input param
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $argA = $request->getQueryParams()['a'] ?? 0;
        $argA = htmlspecialchars($argA, ENT_HTML5, 'UTF-8');

        $argB = $request->getQueryParams()['b'] ?? 0;
        $argB = htmlspecialchars($argB, ENT_HTML5, 'UTF-8');

        return new HtmlResponse('Sum = ' . ((int)$argA + (int)$argB));
    }
}