<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\Tasks;

use Psr\Container\ContainerInterface;

class TaskFactory
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(string $task)
    {
        if (!$this->container->has($task)) {
            throw new \InvalidArgumentException("Use case $task not found in container");
        }
        return $this->container->get($task);
    }
}
