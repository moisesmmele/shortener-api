<?php

namespace Moises\ShortenerApi\Infrastructure\Tasks;

use DI\Container;
use Moises\ShortenerApi\Application\Tasks\TaskInterface;
use Psr\Log\LoggerInterface;

class TaskHandler
{

    /* @description Task Handler class is responsible for handling Task Queueing and deferred execution.
     * My main objective here is to detach task execution (like database cleanups, email sending
     * or what else) from actual Request and Response cycle, which impacts TTFB heavily.
     *
     * The only clear "problem" here is a direct reliance on DI\Container, since I'm using methods
     * not defined by PSR-11 standards. This heavily couples (albeit not too hard to change
     * if needed) this class to a concrete implementation, which violates DIP. Apparently, though,
     * changing container implementations is not common, and it shouldn't be a problem.
     * a "better" pattern would be to implement a Container Adapter, but that would be a lot of work
     * and it's not necessary.
     *
     */

    /* @var array<string, mixed> $tasks*/
    private array $tasks = [];

    public function __construct(
        private readonly Container $container,
        private readonly LoggerInterface $logger,
    ){}


    /** @description simple method to add new tasks to a Queue. It receives an associative array,
     * containing a 'class' key with a class FQN and a 'parameters' key, which should be an associative
     * array with a param name and value structure.
     * @param array<string, mixed> $task
     * @return void
     */
    public function add(array $task): void
    {
        $this->tasks[] = $task;
    }

    /**
     * @description method consumes queue, resolving tasks from classFQN to instantiated, concrete objects.
     * Then, it tries to execute the Task (if it is instance of TaskInterface) using the execute methods.
     * @return void
     */
    public function run(): void
    {
        if (empty($this->tasks)) {
            return;
        }
        //initiate empty results and resolvedTasks array
        $results = [];
        $resolvedTasks = [];

        //iterate through tasks in queue, trying to resolve and instantiate each one
        foreach ($this->tasks as $task) {
            try {

                // use DI\Container::make method to try and instantiate task.
                 $resolved = $this->container->make($task['class'], $task['parameters']);

                 //check if resolved class implements TaskInterface
                 if ($resolved instanceof TaskInterface) {
                     //if it is, push it to resolved tasks array
                     $resolvedTasks[] = $resolved;
                 }
            } catch (\Throwable $exception) {

                //logs possible errors during task resolution
                $message = $exception->getMessage();
                $logContext = [
                    'class_name' => __METHOD__,
                    'exception' => [
                        'message' => $message,
                        'stack_trace' => $exception->getTraceAsString(),
                    ],
                ];
                $this->logger->error("Error occurred while trying to resolve a task: $message", $logContext);
            }
        }

        //iterate through resolved tasks array, trying to execute it.
        foreach ($resolvedTasks as $index => $task) {

            //initiate variables for future logging
            $taskName = (string) $task;
            $start = new \DateTimeImmutable();

            $this->logger->info("Starting task: {$taskName}");

            //try to execute the task
            try {
                $task->execute();
                $status = 'success';
                $exception = null;

            // if it fails, append exception data and log it
            } catch (\Exception $e) {
                $status = 'failed';
                $exception = [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ];
                $this->logger->error("Task failed: {$taskName}", ['exception' => $exception]);
            }

            // get task execution end time and calculate total duration
            $end = new \DateTimeImmutable();
            $duration = $end->getTimestamp() - $start->getTimestamp();

            //append task to results array
            $results[] = [
                'name' => $taskName,
                'status' => $status,
                'duration' => $duration,
                'exception' => $exception
            ];

            // log task completion
            $this->logger->info("Task completed: {$taskName}", [
                'status' => $status,
                'duration' => $duration
            ]);
        }

        // Log total summary
        $this->logger->info('All tasks completed', [
            'total_tasks' => count($results),
            'successful' => count(array_filter($results, fn($r) => $r['status'] === 'success')),
            'failed' => count(array_filter($results, fn($r) => $r['status'] === 'failed'))
        ]);
    }
}