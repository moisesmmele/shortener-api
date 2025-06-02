<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Factories;

use Moises\ShortenerApi\Domain\Contracts\IdentityGeneratorInterface;
use Moises\ShortenerApi\Domain\Contracts\TimestampGeneratorInterface;
use Moises\ShortenerApi\Domain\Entities\Click;
use Moises\ShortenerApi\Domain\Entities\Link;

class ClickFactory
{
    public function __construct(
        private IdentityGeneratorInterface $identityGenerator,
        private TimestampGeneratorInterface $timestampGenerator,
    ){}

    public function create(
        Link $link,
        string $sourceAddress,
        string $referrerAddress
    ): Click
    {
        $click = new Click();
        $uuid = $this->identityGenerator->generate();
        $click->setId($uuid);
        $click->setLinkId($link->getId());
        $click->setSourceIp($sourceAddress);
        $click->setReferrer($referrerAddress);
        $click->setUtcTimestamp($this->timestampGenerator->generate());
        return $click;
    }
}
