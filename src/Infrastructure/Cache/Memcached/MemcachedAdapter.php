<?php

namespace Moises\ShortenerApi\Infrastructure\Cache\Memcached;

use Memcached;
use Psr\SimpleCache\CacheInterface;

class MemcachedAdapter implements CacheInterface
{
    protected \Memcached $memcached;
    protected int $ttl;

    public function __construct(\Memcached $memcached, int $ttl = 60)
    {
        $this->memcached = $memcached;
        $this->ttl = $ttl;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);
        $value = $this->memcached->get($key);
        if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
            return $default;
        }
        return $value;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $this->validateKey($key);
        $ttl = $this->normalizeTtl($ttl);
        return $this->memcached->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);
        return $this->memcached->delete($key);
    }

    public function clear(): bool
    {
        return $this->memcached->flush();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->memcached->getMulti((array) $keys);
    }

    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->validateKey($key);
        }

        $ttl = $this->normalizeTtl($ttl);
        return $this->memcached->setMulti((array) $values, $ttl);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        //use delete multi method from memcache, which returns array of bool results for each key
        $results = $this->memcached->deleteMulti((array) $keys);
        //lookup in results array for any false value,
        //return flipped result from in_array (if false is found, in_array will return true)
        return !in_array(false, $results);
    }

    public function has(string $key): bool
    {
        //validate key
        $this->validateKey($key);

        //get key using memcached
        $this->memcached->get($key);

        //use memcached getResultCode to validate if result was found, compare it
        // with RES_NOTFOUND const, return bool
        return $this->memcached->getResultCode() !== Memcached::RES_NOTFOUND;
    }

    private function validateKey(string $key): void
    {
        //check if key is empty string
        if (empty($key)) {
            throw new \InvalidArgumentException('Key cannot be empty');
        }

        //check if key doesn't have special chars.
        $pattern = '/^[a-zA-Z0-9_-]+$/';
        if (preg_match($pattern, $key)) {
            throw new \InvalidArgumentException('Key has invalid characters');
        }
    }

    protected function normalizeTtl(int|null|\DateInterval $ttl): int
    {
        //if ttl is null return default ttl
        if ($ttl === null) {
            return $this->ttl;
        }

        //if ttl is less than or equal to zero, throw invalidArgumentException
        if ($ttl <= 0) {
            throw new \InvalidArgumentException('TTL must be a positive integer');
        }

        //if Ttl is instance of DateInterval, get current DateTime, add TTL, convert to unix timestamp
        //and subtract current unix timestamp, thus giving a $TTL value in int seconds.
        if ($ttl instanceof \DateInterval) {
            $ttl = (new \DateTimeImmutable())->add($ttl)->getTimestamp() - time();
        }
        return $ttl;
    }

    private function checkSuccess(): bool
    {
        // if last result code is RES_SUCCESS, return true
        if ($this->memcached->getResultCode() === Memcached::RES_SUCCESS) {
            return true;
        };

        return false;
    }
}