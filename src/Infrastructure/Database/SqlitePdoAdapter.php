<?php declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Database;


use Moises\ShortenerApi\Application\Contracts\DatabaseInterface;

class SqlitePdoAdapter implements DatabaseInterface
{
    private \PDO $pdo;
    public function __construct()
    {
        $databasePath = BASE_PATH . "/database/database.sqlite";
        $dsn = "sqlite:$databasePath";
        $this->pdo = new \PDO($dsn);
    }

    public function testConnection()
    {
        $stmt = $this->pdo->prepare("SELECT sqlite_version()");
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $result = $row['sqlite_version()'];
        return $result;
    }
    public function getPdo()
    {
        return $this->pdo;
    }
}