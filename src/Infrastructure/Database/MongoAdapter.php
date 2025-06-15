<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Database;

use MongoDB\Client;

class MongoAdapter implements DatabaseInterface
{
    private Client $client;

    public function __construct(
        // injected through fatory() function in DI bindings
        private string $host,
        private int $port,
        private string $username,
        private string $password,
    )
    {
        $conn_url = "mongodb://{$this->host}:{$this->port}";
        $credentials = [
            'username' => $this->username,
            'password' => $this->password,
            'authSource' => 'admin'
        ];
            $this->client = new Client($conn_url, $credentials);
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
