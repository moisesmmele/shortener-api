<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\ClickDto;
use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\Mappers\ClickMapper;
use Moises\ShortenerApi\Application\Mappers\LinkMapper;
use Moises\ShortenerApi\Domain\Factories\ClickFactory;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;

class RegisterNewClickUseCase
{
    public function __construct(
        private ClickFactory $clickFactory,
        private ClickRepository $clickRepository,
        private ClickMapper $clickMapper,
        private LinkMapper $linkMapper
    ){}
    public function execute(LinkDto $linkDto, string $sourceAddress, string $referrerAddress): ClickDto
    {
        $link = $this->linkMapper->fromDto($linkDto);
        $click = $this->clickFactory->create($link, $sourceAddress, $referrerAddress);
        $this->clickRepository->save($click);
        return $this->clickMapper->toDto($click);
    }
}
