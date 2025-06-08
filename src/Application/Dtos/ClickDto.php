<?php

namespace Moises\ShortenerApi\Application\Dtos;

use Moises\ShortenerApi\Domain\Entities\Click;

final class ClickDto
{
    private string $id;
    private string $link_id;
    private \DateTimeImmutable $utcTimestamp;
    private string $utcTimestampString;
    private string $sourceIp;
    private string $referrer;
    private string $flag;

    public static function fromEntity(Click $click): ClickDto
    {
        $clickDto = new self();
        $clickDto->id = $click->getId();
        $clickDto->link_id = $click->getLinkId();
        $clickDto->utcTimestamp = $click->getUtcTimestamp();
        $clickDto->utcTimestampString = $click->getUtcTimestampString();
        $clickDto->sourceIp = $click->getSourceIp();
        $clickDto->referrer = $click->getReferrer();
        $clickDto->flag = $click->getFlag();
        return $clickDto;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLinkId(): string
    {
        return $this->link_id;
    }

    public function getUtcTimestamp(): \DateTimeImmutable
    {
        return $this->utcTimestamp;
    }

    public function getUtcTimestampString(): string
    {
        return $this->utcTimestampString;
    }

    public function getSourceIp(): string
    {
        return $this->sourceIp;
    }

    public function getReferrer(): string
    {
        return $this->referrer;
    }

    public function getFlag(): string
    {
        return $this->flag;
    }
}
