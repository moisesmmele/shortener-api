<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure;

use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;

class App
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    public function before()
    {
        error_log('this is before request handling');
        error_log((new \DateTimeImmutable())->format('Y-m-d H:i:s.u'));
    }
    public function handle(): void
    {
        $response = $this->router->dispatch();
        $this->router->handleResponse($response);
    }
    public function after(): void
    {
        //add after hooks here, like database clean up, email tasks, etc
        //obs: this only makes sense when running with fastCGI (because of PHP request lifecycle yadayada
        //otherwise you're increasing TTFB and tasks should be handled by cronjobs or dispatched through a queue
        if (IS_FASTCGI) {
            fastcgi_finish_request();
        }
        if (IS_FASTCGI) {
            error_log('this is after request handling');
            error_log((new \DateTimeImmutable())->format('Y-m-d H:i:s.u'));
        }
    }

    public function run()
    {
        $this->before();
        $this->handle();
        $this->after();
    }

}
