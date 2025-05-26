<?php

namespace Moises\ShortenerApi\Infrastructure\Repositories\Mongo;

use Moises\ShortenerApi\Application\Contracts\DatabaseInterface;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;
use Moises\ShortenerApi\Domain\Entities\Click;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Infrastructure\Database\MongoAdapter;
use MongoDB\Client;

class MongoClickRepository implements ClickRepository
{
    private Client $client;

    public function __construct(MongoAdapter $database)
    {
        $this->client = $database->getClient();
    }

    public function save(Click $click): Click
    {
        $collection = $this->client->getCollection('links_db', 'clicks');
        $result = $collection->insertOne([
            'link_id' => $click->getLinkId(),
            'utc_timestamp' => $click->getUtcTimestamp(),
            'source_ip' => $click->getSourceIp(),
            'referrer' => $click->getReferrer(),
            'flag' => $click->getFlag(),
        ]);
        return $click;
    }

    public function findByLink(Link $link): array
    {
        $linkId = $link->getId();
        $collection = $this->client->getCollection('links_db', 'clicks');
        $clicks = $collection->find(['link_id' => $linkId]);
        $clicksObj = [];
        foreach ($clicks as $click) {
            $clickObj = new Click();
            $clickObj->setId($click->getId());
            $clickObj->setLinkId($click->getLinkId());
            $clickObj->setUtcTimestamp($click->getUtcTimestamp());
            $clickObj->setSourceIp($click->getSourceIp());
            $clickObj->setReferrer($click->getReferrer());
            $clickObj->setFlag($click->getFlag());
            $clicksObj[] = $clickObj;
        }
        return $clicksObj;
    }
}