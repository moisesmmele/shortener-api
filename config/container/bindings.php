<?php

use Laminas\Diactoros\ServerRequestFactory;
use League\Route\Router;
use Moises\ShortenerApi\Application\UseCases\Factories\UseCaseFactory;
use Moises\ShortenerApi\Application\UseCases\UseCaseFactoryInterface;
use Moises\ShortenerApi\Domain\Contracts\IdentityGeneratorInterface;
use Moises\ShortenerApi\Domain\Contracts\ShortcodeGeneratorInterface;
use Moises\ShortenerApi\Domain\Contracts\TimestampGeneratorInterface;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Infrastructure\Generators\ShortcodeGenerator;
use Moises\ShortenerApi\Infrastructure\Generators\TimestampGenerator;
use Moises\ShortenerApi\Infrastructure\Generators\UuidV4Generator;
use Moises\ShortenerApi\Infrastructure\Repositories\Mongo\MongoClickRepository;
use Moises\ShortenerApi\Infrastructure\Repositories\Mongo\MongoLinkRepository;
use Moises\ShortenerApi\Infrastructure\Router\LeagueRouterAdapter;
use Moises\ShortenerApi\Infrastructure\Router\RouterInterface;
use Moises\ShortenerApi\Infrastructure\Services\Logger\MongoLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\factory;

return array(
    //obs: autowire generate singletons in PHP-DI
    LoggerInterface::class => autowire(MongoLogger::class),
    //could use autowire, this is manually done for future reference
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
    ServerRequestFactoryInterface::class => autowire(ServerRequestFactory::class),
    IdentityGeneratorInterface::class => autowire(UuidV4Generator::class),
    TimestampGeneratorInterface::class => autowire(TimestampGenerator::class),
    ShortcodeGeneratorInterface::class => autowire(ShortcodeGenerator::class),
);