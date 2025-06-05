<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Entities;

class Click
{
    private string $id;
    private string $linkId;
    private \DateTimeImmutable $utcTimestamp;
    private string $sourceIp;
    private string $referrer;
    private string $flag;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id)) {
            throw new \DomainException('Id should be a valid UUID-v4');
        }
        $this->id = $id;
    }

    public function getLinkId(): string
    {
        return $this->linkId;
    }

    public function setLinkId(string $linkId): void
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $linkId)) {
            throw new \DomainException('Id should be a valid UUID-v4');
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
            $this->referrer = 'localhost';
            return;
        }

        if ($referrer === 'Not Provided') {
            $this->referrer = 'Not Provided';
            return;
        }

        if (!$referrer) {
            throw new \DomainException('Referrer was not provided.');
        }

        if (filter_var($referrer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            Or filter_var($referrer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->referrer = $referrer;
            return;
        }

        if (!filter_var($referrer, FILTER_VALIDATE_URL)) {
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
