<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure;

use Moises\ShortenerApi\Application\Tasks\PerformDatabaseCleanupTask;
use Moises\ShortenerApi\Infrastructure\Router\RouterInterface;
use Moises\ShortenerApi\Infrastructure\Tasks\TaskHandler;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class App
{
    private ServerRequestInterface $request;

    public function __construct(
        private RouterInterface $router,
        private ServerRequestFactoryInterface $requestFactory,
        private TaskHandler $taskHandler,

    ){}
    public function before()
    {
        $this->request = $this->requestFactory->fromGlobals();
    }
    public function handle(): void
    {
       $this->router->route($this->request);
    }
    public function after(): void
    {
        //add 'after' execution code here, like database clean up, email tasks, etc
        //obs: this only makes sense when running with fastCGI (because of PHP request lifecycle yadayada
        //otherwise you're increasing TTFB and tasks should be handled by cronjobs or dispatched through a queue
        if (defined('IS_FASTCGI') && IS_FASTCGI) {
            fastcgi_finish_request();
        }

        $basicTasks = [
            PerformDatabaseCleanupTask::class,
        ];

        foreach ($basicTasks as $task) {
            $this->taskHandler->add($task);
        }

        $this->taskHandler->run();
    }

    public function run()
    {
        $this->before();
        $this->handle();
        $this->after();
    }

}
