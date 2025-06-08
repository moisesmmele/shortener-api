<?php

//TODO: Refactor to use a single main method to write the response
//success, error, notFound and other should be helper methods.
//Also, should implement a way to add headers.

namespace Moises\ShortenerApi\Presentation\Http\Response;

use Moises\ShortenerApi\Presentation\Http\Response\Contracts\ResponseDecoratorInterface;
use Psr\Http\Message\ResponseInterface;

class TextResponseDecorator implements ResponseDecoratorInterface
{
    public function __construct(private ResponseInterface $response){}

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
    public function badRequest(
        string $message = '400 Bad Request',
        array $data = [],
        int $statusCode = 400,
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