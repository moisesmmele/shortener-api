<?php

namespace Moises\ShortenerApi\Application\Repositories\Pdo;

use Moises\ShortenerApi\Domain\Entities\Click;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;

class PdoClickRepository implements ClickRepository
{
    private \PDO $pdo;
    public function __construct()
    {
        $this->pdo = DB;
    }

    public function save(Click $click)
    {
        $this->pdo->beginTransaction();
        try {
            $sql = "INSERT INTO clicks (link_id, utc_timestamp, source_ip, referrer) 
                        VALUES(:link_id, :utc_timestamp, :source_ip, :referrer)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':link_id', $click->getLinkId());
            $stmt->bindValue(':utc_timestamp', $click->getUtcTimestamp());
            $stmt->bindValue(':source_ip', $click->getSourceIp());
            $stmt->bindValue(':referrer', $click->getReferrer());
            $stmt->execute();
            $this->pdo->commit();
        } catch (\Exception $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function findByLink(Link $link)
    {
        try {
            $sql = "SELECT * FROM clicks WHERE link_id = :link_id";
            $stmt = $this->pdo->prepare($sql);
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