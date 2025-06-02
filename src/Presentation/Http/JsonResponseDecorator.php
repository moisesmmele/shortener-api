<?php

namespace Moises\ShortenerApi\Presentation\Http;

use Moises\ShortenerApi\Presentation\Http\Contracts\ResponseDecoratorInterface;
use Psr\Http\Message\ResponseInterface;

class JsonResponseDecorator implements ResponseDecoratorInterface
{

    public function success(string $message = '200 OK', array $data = [], int $statusCode = 200,): ResponseInterface
    {
        // TODO: Implement success() method.
    }

    public function error(string $message = '500 Internal Server Error', array $data = [], int $statusCode = 500,): ResponseInterface
    {
        // TODO: Implement error() method.
    }

    public function notFound(string $message = '404 Not Found', array $data = [], int $statusCode = 404,): ResponseInterface
    {
        // TODO: Implement notFound() method.
    }
}