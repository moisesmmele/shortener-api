<?php

use League\Route\Router;
use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;
use Moises\ShortenerApi\Application\UseCases\Factories\UseCaseFactory;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Infrastructure\Repositories\Mongo\MongoClickRepository;
use Moises\ShortenerApi\Infrastructure\Repositories\Mongo\MongoLinkRepository;
use Moises\ShortenerApi\Infrastructure\Router\LeagueRouterAdapter;
use Moises\ShortenerApi\Infrastructure\Services\Logger\MongoLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\factory;

return array(
    LoggerInterface::class => autowire(MongoLogger::class),
    RouterInterface::class => factory(function (\Psr\Container\ContainerInterface $c) {
        static $routerAdapter = null;
        if ($routerAdapter === null) {
            $router = $c->get(Router::class);
            $logger = $c->get(LoggerInterface::class);
            $routerAdapter = new LeagueRouterAdapter(leagueRouter: $router, container: $c, logger: $logger);
        }
        return $routerAdapter;
    }),
    UseCaseFactoryInterface::class => autowire(UseCaseFactory::class),
    LinkRepository::class => autowire(MongoLinkRepository::class),
    ClickRepository::class => autowire(MongoClickRepository::class),
    ResponseInterface::class => autowire(Laminas\Diactoros\Response::class),
);