<?php

namespace Moises\ShortenerApi\Domain\Logic;

use Moises\ShortenerApi\Domain\Entities\Click;
use Moises\ShortenerApi\Domain\Entities\Link;

class Tracker
{
    public function registerClick(Link $link, string $sourceAddress, string $referrerAddress)
    {
        $click = new Click();
        $click->setLinkId($link->getId());
        $click->generateUtcTimestamp();
        $click->setSourceIp($sourceAddress);
        $click->setReferrer($referrerAddress);
        return $click;
    }
}