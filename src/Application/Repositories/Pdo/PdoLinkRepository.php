<?php

namespace Moises\ShortenerApi\Application\Repositories\Pdo;

use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use PDO;

class PdoLinkRepository implements LinkRepository
{
    private \PDO $pdo;
    public function __construct()
    {
        $this->pdo = DB;
    }

    public function save(Link $link)
    {
        try {
            $this->pdo->beginTransaction();
            $query = "INSERT INTO links ('long_url', 'shortcode') VALUES (:long_url, :short_code)";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':long_url', $link->getLongUrl());
            $stmt->bindValue(':short_code', $link->getShortCode());
            $stmt->execute();
            $this->pdo->commit();
        } catch (\Exception $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function findByShortcode($shortcode)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM links WHERE shortcode = :shortcode");
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
        } catch (\Exception $exception) {
                throw $exception;
        }
    }

    public function getAll()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM links");
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