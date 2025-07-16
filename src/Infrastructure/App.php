<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure;

use Moises\ShortenerApi\Application\Tasks\BasicDemoTask;
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
        // create request from globals using PSR-17 Compliant Request Factory Interface
        $this->request = $this->requestFactory->fromGlobals();
    }
    public function handle(): void
    {
        // Route the request through router, where it will be handled accordingly
        $this->router->route($this->request);
    }
    public function after(): void
    {
        // check if running in fastCGI context via previously set IS_FASTCGI constant
        if (defined('IS_FASTCGI') && IS_FASTCGI) {

            // allow server to return the previously emitted response
            fastcgi_finish_request();
        }

        // declare an array with global tasks
        // in this example, adding a global task by simple class FQN (with no need for parameters)
        // and another with array containing necessary values. Both are valid ways of declaring tasks
        $globalTasks = [
            PerformDatabaseCleanupTask::class,
            [
                'class' => BasicDemoTask::class,
                'parameters' => 'some parameter here'
            ]
        ];

        // add global tasks to task queue
        foreach ($globalTasks as $task) {
            $this->taskHandler->add($task);
        }

        // run TaskHandler
        $this->taskHandler->run();
    }

    // Orchestrate execution flow
    public function run()
    {
        $this->before();
        $this->handle();
        $this->after();
    }
}
