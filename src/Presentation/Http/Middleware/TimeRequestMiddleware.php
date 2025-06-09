<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class TimeRequestMiddleware implements MiddlewareInterface
{

    public function __construct(
        private readonly LoggerInterface $logger,
    ){}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $before = microtime(true);
        $response = $handler->handle($request);
        $after = microtime(true);
        $duration = number_format(($after - $before), 5, '.').' seconds';
        $context = [
            'request' => [
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
                'responseTime' => $duration,
            ],
        ];
        $this->logger->info("logging request time: $duration", $context);
        return $response->withAddedHeader('X-Time', $duration);
    }
}