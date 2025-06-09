<?php

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\ClickDto;
use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\Mappers\LinkMapper;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Factories\ClickFactory;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;

class CollectClicksByLinkUseCase
{
    public function __construct(
        private readonly ClickFactory $trackerService,
        private readonly ClickRepository $clickRepository,
        private readonly LinkMapper $linkMapper
    ){}

    /** @return ClickDto[] */
    public function execute(LinkDto $linkDto): array
    {
        $link = $this->linkMapper->FromDto($linkDto);
        $clicks = $this->clickRepository->findByLink($link);
        $clicksDto = [];
        foreach ($clicks as $click) {
            $clicksDto[] = ClickDto::fromEntity($click);
        }
        return $clicksDto;
    }
}
