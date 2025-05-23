<?php

namespace Moises\ShortenerApi\Infrastructure\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TestController
{
    private $router;
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function index(Request $request): Response
    {
        if (isset($this->router)) {
            $test = true;
        } else { $test = false; }
        return new JsonResponse([
            'is_router_received' => $test,
        ]);
    }
}