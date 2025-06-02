<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Factories\ClickFactory;
use Moises\ShortenerApi\Domain\Factories\LinkFactory;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;

class RegisterNewClickUseCase
{
    private ClickFactory $trackerService;
    private ClickRepository $clickRepository;
    public function __construct(
        private ClickFactory $clickFactory,
        ClickRepository $clickRepository
    ){}
    public function execute(LinkDto $linkDto, string $sourceAddress, string $referrerAddress): void
    {
        $link = $this->clickFactory->registerClick($linkDto, $sourceAddress, $referrerAddress);
        $link->setId($linkDto->getId());
        $link->setShortcode($linkDto->getShortcode());
        $link->setLongUrl($linkDto->getLongUrl());
        $click = $this->trackerService->registerClick($link, $sourceAddress, $referrerAddress);
        $this->clickRepository->save($click);
    }
}
