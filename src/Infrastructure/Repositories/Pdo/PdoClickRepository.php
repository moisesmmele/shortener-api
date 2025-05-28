<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Repositories\Pdo;

use Moises\ShortenerApi\Infrastructure\Database\SqlitePdoAdapter;
use Moises\ShortenerApi\Application\Contracts\DatabaseInterface;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;
use Moises\ShortenerApi\Domain\Entities\Click;
use Moises\ShortenerApi\Domain\Entities\Link;

class PdoClickRepository implements ClickRepository
{
    private DatabaseInterface $database;
    public function __construct(SqlitePdoAdapter $database)
    {
        $this->database = $database;
    }

    public function save(Click $click): Click
    {
        $pdo = $this->database->getPdo();
        $pdo->beginTransaction();
        try {
            $sql = "INSERT INTO clicks (link_id, utc_timestamp, source_ip, referrer) 
                        VALUES(:link_id, :utc_timestamp, :source_ip, :referrer)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':link_id', $click->getLinkId());
            $stmt->bindValue(':utc_timestamp', $click->getUtcTimestamp());
            $stmt->bindValue(':source_ip', $click->getSourceIp());
            $stmt->bindValue(':referrer', $click->getReferrer());
            $stmt->execute();
            $id = $pdo->lastInsertId();
            $pdo->commit();
            $click->setId((int) $id);
            return $click;
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    /** @return Click[] */
    public function findByLink(Link $link): array
    {
        try {
            $pdo = $this->database->getPdo();
            $sql = "SELECT * FROM clicks WHERE link_id = :link_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':link_id', $link->getId());
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $clicks = [];
            foreach ($result as $row) {
                $click = new Click();
                $click->setId($row['id']);
                $click->setLinkId($row['link_id']);
                $click->setUtcTimestamp($row['utc_timestamp']);
                $click->setSourceIp($row['source_ip']);
                $click->setReferrer($row['referrer']);
                $clicks[] = $click;
            }
            return $clicks;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

}
