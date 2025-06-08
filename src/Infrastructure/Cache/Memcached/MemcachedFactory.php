<?php

namespace Moises\ShortenerApi\Infrastructure\Cache\Memcached;

class MemcachedFactory
{
    public static function create(string $server, int $port): Callable
    {
        return function () use ($server, $port) {
            $memcached = new \Memcached();
            $memcached->addServer($server, $port);
            return $memcached;
        };
    }
}