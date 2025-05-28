<?php declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Repositories\Pdo;

use Moises\ShortenerApi\Infrastructure\Database\SqlitePdoAdapter;
use Moises\ShortenerApi\Application\Contracts\DatabaseInterface;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Domain\Entities\Link;
use PDO;

class PdoLinkRepository implements LinkRepository
{
    private DatabaseInterface $database;
    public function __construct(SqlitePdoAdapter $database)
    {
        $this->database = $database;
    }

    public function save(Link $link): Link
    {
        $pdo = $this->database->getPdo();
        try {
            $pdo->beginTransaction();
            $query = "INSERT INTO links ('long_url', 'shortcode') VALUES (:long_url, :short_code)";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':long_url', $link->getLongUrl());
            $stmt->bindValue(':short_code', $link->getShortCode());
            $stmt->execute();
            $pdo->commit();
            $id = $pdo->lastInsertId();
            $link->setId($id);
            return $link;
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function findByShortcode($shortcode): ?Link
    {
        $pdo = $this->database->getPdo();
        try {
            $stmt = $pdo->prepare("SELECT * FROM links WHERE shortcode = :shortcode");
            $stmt->bindValue(':shortcode', $shortcode);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                $link = new Link();
                $link->setId($result['id']);
                $link->setLongUrl($result['long_url']);
                $link->setShortCode($result['shortcode']);
                return $link;
            }
            return null;
        } catch (\Exception $exception) {
                throw $exception;
        }
    }

    public function getAll(): array
    {
        $pdo = $this->database->getPdo();
        try {
            $stmt = $pdo->prepare("SELECT * FROM links");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $links = [];
            if (!empty($result)) {
                foreach ($result as $row) {
                    $link = new Link();
                    $link->setId($row['id']);
                    $link->setLongUrl($row['long_url']);
                    $link->setShortCode($row['shortcode']);
                    $links[] = $link;
                }
            }
            return $links;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}