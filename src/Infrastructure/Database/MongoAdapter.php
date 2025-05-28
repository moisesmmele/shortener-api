<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Database;

use Moises\ShortenerApi\Application\Contracts\DatabaseInterface;
use MongoDB\Client;

class MongoAdapter implements DatabaseInterface
{
    private Client $client;

    public function __construct()
    {
        $conn_url = "{$_ENV['DB_DRIVER']}://{$_ENV['DB_HOST']}:{$_ENV['DB_PORT']}";
        if (isset($_ENV['DB_USE_PASSWORD'])) {
            $credentials = [
                'username' => $_ENV['DB_USER'],
                'password' => $_ENV['DB_PASSWORD']
            ];
            $this->client = new Client($conn_url, $credentials);
        } else {
            $this->client = new Client($conn_url);
        }
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
