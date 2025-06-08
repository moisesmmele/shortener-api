<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Presentation\Http\Middleware;

use DateTimeImmutable;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BasicMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //create a timestamp
        $time = new DateTimeImmutable()->format('Y-m-d H:i:s');
        //delegate request to handler
        $param = $request->getAttribute('param');
        if ($param === 'value') {
            return new JsonResponse([], 404);
        }
        $response = $handler->handle($request);
        //add X-Time header
        return $response->withHeader('X-Time-Middleware', $time);
    }
}
