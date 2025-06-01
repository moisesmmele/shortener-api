<?php

namespace Moises\ShortenerApi\Presentation;

use Moises\ShortenerApi\Presentation\Contracts\ResponseDecoratorInterface;
use Psr\Http\Message\ResponseInterface;

class TextResponseDecorator implements ResponseDecoratorInterface
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function success(
        string $message = '200 OK',
        array $data = [],
        int $statusCode = 200,
    ): ResponseInterface
    {
        $response = $this->response
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus($statusCode);

        $stream = $response->getBody();
        $stream->rewind();
        $stream->write($message);
        return $response;
    }

    public function error(
        string $message = '500 Internal Server Error',
        array $data = [],
        int $statusCode = 500,
    ): ResponseInterface
    {
        $response = $this->response
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus($statusCode);

        $stream = $response->getBody();
        $stream->rewind();
        $stream->write($message);

        return $response;
    }
    public function notFound(
        string $message = '404 Not Found',
        array $data = [],
        int $statusCode = 404,
    ): ResponseInterface
    {
        $response = $this->response
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus($statusCode);

        $stream = $response->getBody();
        $stream->rewind();
        $stream->write($message);

        return $response;
    }
}