<?php

namespace Moises\ShortenerApi\Presentation\Http\Response;

use Psr\Http\Message\ResponseInterface;

class ResponseDecorator
{
    public function __construct(private ResponseInterface $response){}
    public function redirect($url, int $statusCode = 302): ResponseInterface
    {
        $response = $this->response->withStatus($statusCode)->withHeader('Location', $url);
        return $response;
    }
}