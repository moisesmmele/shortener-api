<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BasicMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authkey = $request->getHeader('Authorization');
        if (!$authkey === '1234') {
            return $handler->handle($request)->withStatus(401);
        }
        $response = $handler->handle($request);
        return $response->withHeader('X-Basic-Middleware', 'Processed');
    }
}
