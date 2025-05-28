<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Domain\Repositories\ClickRepository;
use Moises\ShortenerApi\Domain\Services\TrackerService;
use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Domain\Entities\Link;

class RegisterNewClickUseCase
{
    private TrackerService $trackerService;
    private ClickRepository $clickRepository;
    public function __construct(TrackerService $trackerService, ClickRepository $clickRepository)
    {
        $this->clickRepository = $clickRepository;
        $this->trackerService = $trackerService;
    }
    public function execute(LinkDto $linkDto, string $sourceAddress, string $referrerAddress): void
    {
        $link = new Link();
        $link->setId($linkDto->getId());
        $link->setShortcode($linkDto->getShortcode());
        $link->setLongUrl($linkDto->getLongUrl());
        $click = $this->trackerService->registerClick($link, $sourceAddress, $referrerAddress);
        $this->clickRepository->save($click);
    }
}
