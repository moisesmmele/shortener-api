<?php

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\ClickDto;
use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Factories\ClickFactory;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;

class CollectClicksByLinkUseCase
{
    private ClickFactory $trackerService;
    private ClickRepository $clickRepository;

    public function __construct(ClickFactory $trackerService, ClickRepository $clickRepository)
    {
        $this->trackerService = $trackerService;
        $this->clickRepository = $clickRepository;

    }

    /** @return ClickDto[] */
    public function execute(LinkDto $linkDto): array
    {
        $link = new Link();
        $link->setId($linkDto->getId());
        $link->setShortcode($linkDto->getShortcode());
        $link->setLongUrl($linkDto->getLongUrl());

        $clicks = $this->clickRepository->findByLink($link);
        $clicksDto = [];
        foreach ($clicks as $click) {
            $clicksDto[] = ClickDto::fromEntity($click);
        }
        return $clicksDto;
    }
}
