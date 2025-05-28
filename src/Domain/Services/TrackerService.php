<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Services;

use Moises\ShortenerApi\Domain\Entities\Click;
use Moises\ShortenerApi\Domain\Entities\Link;

class TrackerService
{
    public function registerClick(Link $link, string $sourceAddress, string $referrerAddress): Click
    {
        $click = new Click();
        $click->setLinkId($link->getId());
        $click->setSourceIp($sourceAddress);
        $click->setReferrer($referrerAddress);
        $click->generateUtcTimestamp(); // Entity method

        // Additional domain logic example:
        if ($this->isSuspiciousClick($click)) {
            //TODO: implement REAL business logic inside the TrackerService;
            // This is here just so it can actually be considered a service
            $click->setFlag('suspicious');
        }

        return $click;
    }

    private function isSuspiciousClick(Click $click): bool
    {
        // Placeholder logic; replace with real rules
        return $click->getSourceIp() === '127.0.0.1';
    }
}
