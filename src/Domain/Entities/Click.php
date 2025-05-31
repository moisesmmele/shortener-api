<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Entities;

class Click
{
    private int $id;
    private int $linkId;
    private \DateTimeImmutable $utcTimestamp;
    private string $sourceIp;
    private string $referrer;
    private string $flag;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        if ($id <= 0 || $id === null) {
            throw new \DomainException('Id cannot be negative or zero');
        }
        $this->id = $id;
    }

    public function getLinkId(): int
    {
        return $this->linkId;
    }

    public function setLinkId(int $linkId): void
    {
        if ($linkId <= 0 || $linkId === null) {
            throw new \DomainException('Link id cannot be negative or zero');
        }

        $this->linkId = $linkId;
    }

    public function getUtcTimestamp(): \DateTimeImmutable
    {
        return $this->utcTimestamp;
    }

    public function getUtcTimestampString(): string
    {
        return $this->utcTimestamp->format('Y-m-d H:i:s');
    }
    public function setUtcTimestamp(string|\DateTimeImmutable $timestamp): void
    {
        if (is_string($timestamp)) {
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) ([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $timestamp)) {
                throw new \DomainException("Invalid timestamp format. Should be YYYY-MM-DD HH:MM:SS");
            }
            $timestamp = new \DateTimeImmutable($timestamp);
        }

        $this->utcTimestamp = $timestamp;
    }

    public function generateUtcTimestamp(): void
    {
        $this->setUtcTimestamp(gmdate('Y-m-d H:i:s'));
    }
    public function getSourceIp(): string
    {
        return $this->sourceIp;
    }

    public function setSourceIp(string $sourceIp): void
    {

        if (!filter_var($sourceIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
            !filter_var($sourceIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new \DomainException("Source IP Address does not match pattern");
        }
        $this->sourceIp = $sourceIp;
    }

    public function getReferrer(): string
    {
        return $this->referrer;
    }

    public function setReferrer(string $referrer): void
    {
        if ($referrer === 'localhost' || $referrer === '127.0.0.1' || $referrer === '::1') {
            $this->referrer = $referrer;
            return;
        }

        if (!$referrer) {
            throw new \DomainException('Referrer was not provided.');
        }

        if (!filter_var($referrer, FILTER_VALIDATE_URL) &&
            !filter_var($referrer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
            !filter_var($referrer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new \DomainException("Invalid referrer.");
        }

        $parts = parse_url($referrer);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            throw new \DomainException("Invalid referrer. Missing scheme or host.");
        }

        if (preg_match('/\s/', $referrer)) {
            throw new \DomainException("Invalid referrer. Link contains invalid characters.");
        }

        $this->referrer = $referrer;
    }
    public function getFlag(): string
    {
        return $this->flag;
    }
    public function setFlag(string $flag): void
    {
        $this->flag = $flag;
    }

}
