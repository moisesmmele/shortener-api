<?php

namespace Moises\ShortenerApi\Presentation\Http\Factories;
use Moises\ShortenerApi\Presentation\Http\Contracts\ResponseDecoratorInterface;
use Moises\ShortenerApi\Presentation\Http\JsonResponseDecorator;
use Moises\ShortenerApi\Presentation\Http\TextResponseDecorator;
use Psr\Http\Message\ResponseInterface;

class ResponseDecoratorFactory
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function text(): ResponseDecoratorInterface
    {
        return new TextResponseDecorator($this->response);
    }

    public function json(): ResponseDecoratorInterface
    {
        return new JsonResponseDecorator($this->response);
    }
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}