<?php

namespace Moises\ShortenerApi\Presentation\Http;

use Moises\ShortenerApi\Presentation\Http\Contracts\ResponseDecoratorInterface;
use Psr\Http\Message\ResponseInterface;

class JsonResponseDecorator implements ResponseDecoratorInterface
{
    public function __construct(private ResponseInterface $response){}

    public function success(
        string $message = '200 OK',
        array $data = [],
        int $statusCode = 200,
    ): ResponseInterface
    {
        $payload = [
            'status_code' => $statusCode,
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        $response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);

        $stream = $response->getBody();
        $stream->rewind();
        $stream->write($this->encode($payload));
        return $response;
    }

    public function error(
        string $message = '500 Internal Server Error',
        array $data = [],
        int $statusCode = 500,
    ): ResponseInterface
    {
        $payload = [
            'status_code' => $statusCode,
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];

        $response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);

        $stream = $response->getBody();
        $stream->rewind();
        $stream->write($this->encode($payload));
        return $response;
    }

    public function notFound(
        string $message = '404 Not Found',
        array $data = [],
        int $statusCode = 404,
    ): ResponseInterface
    {
        $payload = [
            'status_code' => $statusCode,
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];

        $response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);

        $stream = $response->getBody();
        $stream->rewind();
        $stream->write($this->encode($payload));
        return $response;
    }

    public function badRequest(
        string $message = '400 Bad Request',
        array $data = [],
        int $statusCode = 400,
    ): ResponseInterface
    {
        $payload = [
            'status_code' => $statusCode,
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];

        $response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);

        $stream = $response->getBody();
        $stream->rewind();
        $stream->write($this->encode($payload));
        return $response;
    }

    public function encode(array $payload)
    {
        return json_encode($payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION
        );
    }
}