<?php

namespace Moises\ShortenerApi\Application\UseCases\Factories;

use Psr\Container\ContainerInterface;
use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;

class UseCaseFactory implements UseCaseFactoryInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(string $useCase)
    {
        if (!$this->container->has($useCase)) {
            throw new \InvalidArgumentException("Use case $useCase not found in container");
        }
        return $this->container->get($useCase);
    }
}
