<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Repositories\Mongo;

use Moises\ShortenerApi\Infrastructure\Database\MongoAdapter;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Domain\Entities\Link;
use MongoDB\Client;

class MongoLinkRepository implements LinkRepository
{
    private Client $client;
    public function __construct(MongoAdapter $database)
    {
        $this->client = $database->getClient();
    }

    public function save(Link $link): Link
    {
        $collection = $this->client->getCollection($_ENV['DB_NAME'], 'links');
        $result = $collection->insertOne([
            'link_id' => $link->getId(),
            'shortcode' => $link->getShortcode(),
            'long_url' => $link->getLongUrl(),
            'created_at' => $link->getCreatedAtString(),
        ]);
        return $link;
    }

    public function findByShortcode(string $shortcode): ?Link
    {
        $collection = $this->client->getCollection($_ENV['DB_NAME'], 'links');
        $result = $collection->findOne(['shortcode' => $shortcode]);

        if (!$result) {
            return null;
        }

        $link = new Link();
        $link->setId($result['link_id']);
        $link->setShortcode($result['shortcode']);
        $link->setLongUrl($result['long_url']);
        $link->setCreatedAt($result['created_at']);
        return $link;
    }

    public function getAll(): array
    {
        $collection = $this->client->getCollection($_ENV['DB_NAME'], 'links');
        $results = $collection->find();
        $links = [];
        foreach ($results as $result) {
            $link = new Link();
            $link->setId($result['link_id']);
            $link->setShortcode($result['shortcode']);
            $link->setLongUrl($result['long_url']);
            $links[] = $link;
        }
        return $links;
    }
}
