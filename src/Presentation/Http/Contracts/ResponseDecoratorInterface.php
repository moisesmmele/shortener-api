<?php

//TODO: Refactor to use a single main method to write the response
//success, error, notFound and other should be helper methods.
//Also, should implement a way to add headers.

namespace Moises\ShortenerApi\Presentation\Http\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseDecoratorInterface
{
    public function success(
        string $message = '200 OK',
        array $data = [],
        int $statusCode = 200,
    ): ResponseInterface;

    public function error(
        string $message = '500 Internal Server Error',
        array $data = [],
        int $statusCode = 500,
    ): ResponseInterface;

    public function notFound(
        string $message = '404 Not Found',
        array $data = [],
        int $statusCode = 404,
    ): ResponseInterface;
}