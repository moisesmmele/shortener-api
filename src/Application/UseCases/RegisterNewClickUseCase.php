<?php

namespace Moises\ShortenerApi\Application\UseCases;

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
    public function execute(Link $link, string $sourceAddress, string $referrerAddress)
    {
        $click = $this->trackerService->registerClick($link, $sourceAddress, $referrerAddress);
        $this->clickRepository->save($click);
    }
}