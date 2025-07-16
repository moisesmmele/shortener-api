<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Repositories\Pdo;

use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Infrastructure\Database\DatabaseInterface;
use Moises\ShortenerApi\Infrastructure\Database\SqlitePdoAdapter;
use PDO;

class PdoLinkRepository implements LinkRepository
{
    public function __construct(
        private readonly SqlitePdoAdapter $pdoAdapter
    ){}

    public function save(Link $link): Link
    {
        $pdo = $this->pdoAdapter->getPdo();
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
        $pdo = $this->pdoAdapter->getPdo();
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
        $pdo = $this->pdoAdapter->getPdo();
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

    public function delete(Link $link): void
    {
        $pdo = $this->pdoAdapter->getPdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("DELETE FROM links WHERE id = :id");
            $stmt->bindValue(':id', $link->getId());
            $stmt->execute();
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
