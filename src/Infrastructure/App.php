<?php

namespace Moises\ShortenerApi\Infrastructure;

use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;

class App
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    public function handle(): void
    {
        $response = $this->router->dispatch();
        $this->router->handleResponse($response);
    }
}