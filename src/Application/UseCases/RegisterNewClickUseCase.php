<?php

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Services\TrackerService;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;

class RegisterNewClickUseCase
{
    private TrackerService $trackerService;
    private ClickRepository $clickRepository;
    public function __construct( TrackerService $trackerService, ClickRepository $clickRepository)
    {
        $this->clickRepository = $clickRepository;
        $this->trackerService = $trackerService;
    }
    public function execute(LinkDto $linkDto, string $sourceAddress, string $referrerAddress)
    {
        $link = new Link();
        $link->setId($linkDto->getId());
        $link->setShortcode($linkDto->getShortcode());
        $link->setLongUrl($linkDto->getLongUrl());
        $click = $this->trackerService->registerClick($link, $sourceAddress, $referrerAddress);
        $this->clickRepository->save($click);
    }
}