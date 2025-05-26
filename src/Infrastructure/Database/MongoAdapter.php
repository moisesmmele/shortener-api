<?php

namespace Moises\ShortenerApi\Infrastructure\Database;


use Moises\ShortenerApi\Application\Contracts\DatabaseInterface;
use MongoDB\Client;

class MongoAdapter implements DatabaseInterface
{
    private Client $client;

    public function __construct()
    {
        $dsn = "mongodb://172.16.99.86:27017";
        $this->client = new Client($dsn);
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}