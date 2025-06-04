<?php

namespace Moises\ShortenerApi\Application\Mappers;

use Moises\ShortenerApi\Application\Dtos\ClickDto;
use Moises\ShortenerApi\Domain\Entities\Click;

class ClickMapper
{
    public function fromDto(ClickDto $clickDto): Click
    {
        $click = new Click();
        $click->setId($clickDto->getId());
        $click->setLinkId($clickDto->getLinkId());
        $click->setUtcTimestamp($clickDto->getUtcTimestamp());
        $click->setReferrer($clickDto->getReferrer());
        $click->setSourceIp($clickDto->getSourceIp());
        $click->setFlag($clickDto->getFlag());
        return $click;
    }

    public function toDto(Click $click): ClickDto
    {
        return ClickDto::fromEntity($click);
    }
}